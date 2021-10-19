<?php

require_once(dirname(__FILE__) . "/../include/init.php");

$stmt = GetStatement(DB_CONTROL);
$query = "SELECT * FROM option_value_history WHERE value = 'W' OR value = 'Wmax'
            ORDER BY level, entity_id";
$historyList = $stmt->FetchList($query);

$resultList = [];
$queryList = [];
$companyUnitList = [];
$employeeList = [];
foreach ($historyList as $historyEntry) {
    $optionValue = Option::GetCurrentValue(
        $historyEntry["level"],
        $historyEntry["option_id"],
        $historyEntry["entity_id"]
    );
    if ($optionValue != "W" && $optionValue != "Wmax") {
        continue;
    }

    /*$inArchive = $historyEntry["level"] == OPTION_LEVEL_EMPLOYEE
        ? Employee::GetEmployeeField($historyEntry["entity_id"], "archive")
        : CompanyUnit::GetPropertyValue("archive", $historyEntry["entity_id"]);
    if ($inArchive == "Y") {
        continue;
    }*/

    $option = new Option("product");
    $option->LoadByID($historyEntry["option_id"]);

    /*$contract = new Contract("product");
    $contractExists = $contract->ContractExist(
        $historyEntry["level"],
        $option->GetProperty("product_id"),
        $historyEntry["entity_id"],
        GetCurrentDate()
    );*/
    $contractExists = true;
    if (!$contractExists || isset($resultList[$historyEntry["entity_id"] . "_" . $historyEntry["option_id"]])) {
        continue;
    }

    $resultList[$historyEntry["entity_id"] . "_" . $historyEntry["option_id"]] = 1;

    $queryList[] = "(" . Connection::GetSQLString($historyEntry["level"]) . ",
                    " . intval($historyEntry["entity_id"]) . ",
                    " . $historyEntry["option_id"] . ",
                    " . Connection::GetSQLDateTime(GetCurrentDate()) . ",
                    " . SERVICE_USER_ID . ",
                    NULL,
                    " . Connection::GetSQLString("admin") . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ")";
    if ($historyEntry["level"] == OPTION_LEVEL_EMPLOYEE) {
        if (!isset($employeeList[$historyEntry["entity_id"]])) {
            $companyUnitID = Employee::GetEmployeeField(
                $historyEntry["entity_id"],
                "company_unit_id"
            );
            $employeeList[$historyEntry["entity_id"]] = [
                "company title" => CompanyUnit::GetTitleByID($companyUnitID),
                "employee name" => Employee::GetNameByID($historyEntry["entity_id"]),
                "option_list" => [],
            ];
        }

        if (
            in_array(
                Option::GetCodeByOptionID($historyEntry["option_id"]),
                $employeeList[$historyEntry["entity_id"]]["option_list"]
            )
        ) {
            continue;
        }

        $employeeList[$historyEntry["entity_id"]]["option_list"][] =
            Option::GetCodeByOptionID($historyEntry["option_id"]);
    } else {
        if (!isset($companyUnitList[$historyEntry["entity_id"]])) {
            $companyUnitList[$historyEntry["entity_id"]] = [
                "company title" => CompanyUnit::GetTitleByID($historyEntry["entity_id"]),
                "option_list" => [],
            ];
        }
        if (
            in_array(
                Option::GetCodeByOptionID($historyEntry["option_id"]),
                $companyUnitList[$historyEntry["entity_id"]]["option_list"]
            )
        ) {
            continue;
        }

        $companyUnitList[$historyEntry["entity_id"]]["option_list"][] =
            Option::GetCodeByOptionID($historyEntry["option_id"]);
    }
}
echo "count companies - " . count($companyUnitList);
print_r($companyUnitList);
echo "count employees - " . count($employeeList);
print_r($employeeList);

if (!empty($queryList)) {
    $query = "INSERT INTO option_value_history
                (level, entity_id, option_id, date_from, user_id, value, created_from, created)
                    VALUES " . implode(", ", $queryList);
    echo $query;
    $stmt->Execute($query);
}
