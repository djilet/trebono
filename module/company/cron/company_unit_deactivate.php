<?php

/**
 * Deactivates employees with end_date of base module passed yesterday. Should be runned every night.
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");
$request = new LocalObject(array_merge($_GET, $_POST));

$module = "company";

if ($request->GetProperty("date")) {
    $date = $request->GetProperty("date");
} else {
    $date = GetCurrentDate();
}
$yesterday = date("Y-m-d", strtotime($date . " -1 day"));

$companyUnitIDs = CompanyUnitList::GetActiveCompanyUnitIDs();

$cronLog = "Started deactivating company units.</br>";
$type = "deactivate";
$operationID = Operation::SaveCron(null, $cronLog, $type);

$countDeactivated = 0;

if (count($companyUnitIDs) > 0) {
    foreach ($companyUnitIDs as $companyUnitID) {
        $baseModuleContract = new Contract("product");
        $baseModuleContract->LoadContractForDate(
            OPTION_LEVEL_COMPANY_UNIT,
            $companyUnitID,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            $yesterday
        );

        if (
            !$baseModuleContract->GetProperty("contract_id")
            || strtotime($baseModuleContract->GetProperty("end_date")) != strtotime($yesterday)
        ) {
            continue;
        }

        $companyUnit = new CompanyUnit($module);
        $companyUnit->EndByCron($companyUnitID, $yesterday);
        $cronLog .= "Deactivated company unit " . CompanyUnit::GetTitleByID($companyUnitID) . "</br>";
        Operation::SaveCron($operationID, $cronLog, $type);
        $countDeactivated++;
    }
}

$cronLog .= "Deactivated $countDeactivated company units in total.</br>";
Operation::SaveCron($operationID, $cronLog, $type, null, null, true);
