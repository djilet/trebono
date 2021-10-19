<?php

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");
$request = new LocalObject(array_merge($_GET, $_POST));

$module = "company";
$moduleProduct = "product";
$type = "voucher";

if ($request->GetProperty("date")) {
    $date = $request->GetProperty("date");
} else {
    $date = GetCurrentDate();
}

$cronLog = "Started creating food vouchers.</br>";
$errorList = "";
$totalCountVoucher = 0;
$usedIDs = array();
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);
$foodMainProductID = Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN);
$foodProductGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);

$employeeList = array();
if ($request->GetProperty("employee_id")) {
    $optionValue = Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION,
        $request->GetProperty("employee_id"), $date);
    if ($optionValue == "Y") {
        $employeeList[] = $request->GetProperty("employee_id");
    }
} else {
    $employeeList = EmployeeList::GetEmployeeIDsForGenerationVouchers($foodProductGroupID);
}

foreach ($employeeList as $employeeID) {
    $countVoucher = 0;

    $voucher = new Voucher($module);

    $countByOption = Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH, $employeeID, $date);
    $specificProductGroup = SpecificProductGroupFactory::Create($foodProductGroupID);

    $voucherMap = $specificProductGroup->MapVoucherListToMonth($employeeID, $foodProductGroupID, $voucher);

    $month = date("Y-m", strtotime($date));
    $countFromMap = isset($voucherMap[$month]["voucher_ids"]) && $voucherMap[$month]["voucher_ids"] ? count($voucherMap[$month]["voucher_ids"]) : 0;

    $countForCreate = $countByOption - $countFromMap;

    if ($countForCreate > 0) {
        $voucher->SetProperty("employee_id", $employeeID);
        $contract = new Contract($moduleProduct);
        $contract->LoadLatestActiveContract(OPTION_LEVEL_EMPLOYEE, $employeeID, $foodMainProductID);
        if (date("m", strtotime($contract->GetProperty("start_date"))) == date("m",
                strtotime($date)) && strtotime($contract->GetProperty("start_date")) > strtotime(date("1.m.Y",
                strtotime($date)))) {
            $voucher->SetProperty("voucher_date", $contract->GetProperty("start_date"));
        } else {
            $voucher->SetProperty("voucher_date", date("1.m.Y", strtotime($date)));
        }

        $voucher->SetProperty("group_id", $foodProductGroupID);

        $unit = $specificProductGroup->GetUnit(new Receipt(
            "receipt",
            array(
                "document_date" => $date,
                "employee_id" => $voucher->GetProperty("employee_id")
            )
        ), "admin");

        $voucher->SetProperty("amount", $unit);
        $voucher->SetProperty("end_date", date("31.12.Y", strtotime($date . "+ 3 year")));
        $voucher->SetProperty("reason", "Essensmarken");
        $voucher->SetProperty("IsFoodVoucherCron", 1);
        $voucher->SetProperty("count", $countForCreate);
        $voucher->SetProperty("created_user_id", ESSEN_GUTSCHEINE);
        if ($voucher->Validate()) {
            $countVouchers = $voucher->GetProperty("count");
            $voucher->RemoveProperty("count");

            for ($i = 0; $i < $countVouchers; $i++) {
                if ($voucher->Save()) {
                    $voucher->RemoveProperty("voucher_id");

                    if ($voucher->GetProperty("file")) {
                        $countVoucher++;
                    }
                }
            }
        }

        if ($countVoucher > 0) {
            $employee = new Employee($module);
            $employee->LoadByID($employeeID);
            $voucher->SendVoucherToEmail($employee, $voucher->GetProperty("group_id"), $countVoucher);
            $usedIDs[] = [
                "employee_id" => $employeeID,
                "name" => $employee->GetProperty("first_name") . " " . $employee->GetProperty("last_name")
            ];
        }

        if ($voucher->HasErrors()) {
            $errorList .= "Employee ID " . $employeeID . ": " . $voucher->GetErrorsAsString("</br>") . "</br>";
        }

        $totalCountVoucher += $countVoucher;
    }
}

$cronLog .= "Created " . $totalCountVoucher . " food vouchers.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, $usedIDs, true);