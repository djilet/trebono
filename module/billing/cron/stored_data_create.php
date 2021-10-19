<?php

/**
 * Creates new data stored. Should be run daily.
 */

set_time_limit(60 * 60 * 3);

require_once(dirname(__FILE__) . "/../../../include/init.php");
require_once(dirname(__FILE__) . "/../init.php");

$module = "billing";
$request = new LocalObject(array_merge($_GET, $_POST));

$languageCode = $request->GetProperty("language_code") ?: "en";

$date = $request->GetProperty("date") ?: date("Y-m-d");

$cronLog = "Started creating stored data for $date.</br>";
$errorList = "";
$type = "stored_data_create";
$storedDataExistMessages = [];

$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

$countExist = 0;
$countGeneralCreated = 0;
$countIndividualCreated = 0;
$countSent = 0;

//get company unit id and service option values
$companyUnitsData = CompanyUnitList::GetCompanyUnitDataForStoredDataCreation(
    $date,
    $request->GetProperty("company_unit_id")
);

$cronLog .= "Stored data are being created for " . count($companyUnitsData) . " company units.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList);
Operation::SaveCronStatus($operationID, "Start generating");

$storeData = new StoredData($module);

foreach ($companyUnitsData as $companyUnitData) {
    $countCreatedByCompanyUnit = 0;

    //periods for manual or cron generation
    if ($request->GetProperty("period_range") && $request->GetProperty("frequency")) {
        [$dateFrom, $dateTo] = explode(" - ", $request->GetProperty("period_range"));
        $periods = $storeData->GetPeriodsForStoredDataManualGeneration(
            $request->GetProperty("frequency"),
            date("Y-m-d", strtotime($dateFrom)),
            date("Y-m-d", strtotime($dateTo))
        );
    } else {
        $periods = $storeData->GetPeriodsForStoredDataCronGeneration(
            $companyUnitData['frequency'],
            $companyUnitData['payroll_month'],
            $date
        );
    }

    foreach ($periods as $period) {
        //general archive
        if (StoredData::StoredDataExists(
            $companyUnitData['company_unit_id'],
            "all",
            $period['dateFrom'],
            $period['dateTo'])
        ) {
            $countExist++;
            $storedDataExistMessages[] = date("d.m.Y", strtotime($period['dateFrom'])) . " - " .
                date("d.m.Y", strtotime($period['dateTo'])) . ", " .
                GetTranslation("all-employee", $module, [], $languageCode);
            continue;
        }
        $storeData->LoadFromArray([
            "company_unit_id" => $companyUnitData['company_unit_id'],
            "date_from" => $period['dateFrom'],
            "date_to" => $period['dateTo'],
            "employees" => $companyUnitData['employees_for_general'],
            "general" => true,
            "cron" => $request->GetProperty("is_cron") == "N" ? "N" : "Y",
        ]);

        if ($storeData->Create()) {
            $countGeneralCreated++;
            if ($storeData->GenerateStoredDataZIP($operationID)) {
                if ($storeData->Send()) {
                    $countSent++;
                }
            }
        }

        //individual archives
        if ($companyUnitData['employees_for_individual']) {
            foreach ($companyUnitData['employees_for_individual'] as $employeeID) {
                if (StoredData::StoredDataExists(
                    $companyUnitData['company_unit_id'],
                    $employeeID, $period['dateFrom'],
                    $period['dateTo'])
                ) {
                    $countExist++;
                    $storedDataExistMessages[] = date("d.m.Y", strtotime($period['dateFrom'])) . " - " .
                        date("d.m.Y", strtotime($period['dateTo'])) . ", " . Employee::GetNameByID($employeeID);
                    continue;
                }

                $storeData = new StoredData($module);
                $storeData->LoadFromArray([
                    "company_unit_id" => $companyUnitData['company_unit_id'],
                    "date_from" => $period['dateFrom'],
                    "date_to" => $period['dateTo'],
                    "employees" => [$employeeID],
                    "cron" => $request->GetProperty("is_cron") == "N" ? "N" : "Y",
                ]);

                if (!$storeData->Create()) {
                    continue;
                }

                $countGeneralCreated++;
                $storeData->GenerateStoredDataZIP($operationID);
            }
        }

        if (!$storeData->HasErrors()) {
            continue;
        }

        $errorList .= $storeData->GetErrorsAsString("</br>");
    }
}

$cronLog .= "Created $countGeneralCreated stored data, $countIndividualCreated individual stored data. 
    Sent $countSent emails. $countExist already existed.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
Operation::SaveCronStatus($operationID, "Finish generating");

//messages for manual generation
if ($request->GetProperty("is_cron") == "N") {
    if (count($storedDataExistMessages) > 0) {
        echo json_encode([
            "HTML" => GetTranslation("stored-data-already-formed-error", $module, [], $languageCode) . "</br>" .
                implode("</br>", $storedDataExistMessages)
        ]);
    } else {
        echo json_encode("success");
    }
}
