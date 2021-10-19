<?php

/**
 * Activate employees and company units with today's start_date of base module. Should be runned every night.
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

// company units activation
$companyUnitIDs = CompanyUnitList::GetActiveCompanyUnitIDs(
    true,
    Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
    $date,
    true
);

$cronLog = "Started activating company units.</br>";
$type = "deactivate";
$operationID = Operation::SaveCron(null, $cronLog, $type);
$activatedCompanyUnitsCount = 0;

if (count($companyUnitIDs) > 0) {
    $companyUnitList = new CompanyUnitList($module);
    $activatedCompanyUnitsCount = $companyUnitList->Activate($companyUnitIDs, SERVICE_USER_ID);

    foreach ($companyUnitIDs as $companyUnitID) {
        $cronLog .= "Activated company unit " . CompanyUnit::GetTitleByID($companyUnitID) . "</br>";
    }

    Operation::SaveCron($operationID, $cronLog, $type);
}

$cronLog .= "Activated " . $activatedCompanyUnitsCount . " company units in total.</br>";

// employees activation
$employeeIDs = EmployeeList::GetActiveEmployeeIDs(true, null, false, false, false, $date, true);

$cronLog .= "Started activating employees.</br>";
Operation::SaveCron($operationID, $cronLog, $type);
$activatedEmployeesCount = 0;

if (count($employeeIDs) > 0) {
    $employeeList = new EmployeeList($module);
    $activatedEmployeesCount = $employeeList->Activate($employeeIDs, SERVICE_USER_ID);

    foreach ($employeeIDs as $employeeID) {
        $cronLog .= "Activated employee " . Employee::GetNameByID($employeeID) . "</br>";
    }

    Operation::SaveCron($operationID, $cronLog, $type);
}

$cronLog .= "Activated " . $activatedEmployeesCount . " employees in total.</br>";

Operation::SaveCron($operationID, $cronLog, $type, null, null, true);
