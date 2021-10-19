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

$employeeIDs = EmployeeList::GetActiveEmployeeIDs(false, null, false, false, true);

$cronLog = "Started deactivating employees.</br>";
$type = "deactivate";
$operationID = Operation::SaveCron(null, $cronLog, $type);

$countDeactivated = 0;

if (count($employeeIDs) > 0) {
    foreach ($employeeIDs as $employeeID) {
        $baseModuleContract = new Contract("product");
        $baseModuleContract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employeeID,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            $yesterday
        );

        if (!$baseModuleContract->GetProperty("contract_id") || strtotime($baseModuleContract->GetProperty("end_date")) != strtotime($yesterday)) {
            continue;
        }

        $employee = new Employee($module);
        $employee->EndByCron($employeeID, $yesterday);
        $cronLog .= "Deactivated employee " . Employee::GetNameByID($employeeID) . "</br>";
        Operation::SaveCron($operationID, $cronLog, $type);
        $countDeactivated++;
    }
}

$cronLog .= "Deactivated $countDeactivated employees in total.</br>";
Operation::SaveCron($operationID, $cronLog, $type, null, null, true);
