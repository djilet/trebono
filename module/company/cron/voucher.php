<?php

/**
 * Removes receipts without images. Should be runned every 5 minutes.
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");

$module = "company";

$cronLog = "Started generating and sending out vouchers.</br>";
$type = "voucher";

$countVoucherUpdate = 0;
$countVoucherFound = 0;
$totalCountFoodVoucher = 0;
$countSent = 0;
$errorList = "";
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

$employeeList = EmployeeList::GetActiveEmployeeIDs(false, null, false, true);

$employeeVoucherList = new VoucherList($module);

$employee = new Employee($module);

foreach ($employeeList as $employeeID) {
    $countFoodVoucher = 0;

    $employee->LoadByID($employeeID);

    $employeeVoucherList->LoadVoucherListByEmployeeID($employeeID, false, "Y", false, true);

    $voucher = new Voucher($module);

    foreach ($employeeVoucherList->GetItems() as $employeeVoucher) {
        if (
            strtotime($employeeVoucher["voucher_date"]) > strtotime(GetCurrentDate()) || strtotime($employeeVoucher["recurring_end_date"]) < strtotime(GetCurrentDate()) ||
            (
                (date(
                    'd',
                    strtotime($employeeVoucher["voucher_date"])
                ) != date('d') || $employeeVoucher["recurring_frequency"] != "monthly") &&
                (date('d', strtotime($employeeVoucher["voucher_date"])) != date('d') || abs(date('n') - date(
                    'n',
                    strtotime($employeeVoucher["voucher_date"])
                )) % 3 != 0 || $employeeVoucher["recurring_frequency"] != "quarterly") &&
                (date(
                    'dm',
                    strtotime($employeeVoucher["voucher_date"])
                ) != date('dm') || $employeeVoucher["recurring_frequency"] != "yearly")
            )
        ) {
            continue;
        }

        $voucher->LoadByID($employeeVoucher["voucher_id"]);

        $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        if (in_array($employeeVoucher["group_id"], array_column($voucherProductGroupList, "group_id"))) {
            $voucher->SetProperty("end_date", date("31.12.Y", strtotime(date("Y-m-d") . "+ 3 year")));
        } else {
            $dateDiff = date_diff(
                date_create($employeeVoucher["end_date"]),
                date_create($employeeVoucher["voucher_date"])
            );
            $voucher->SetProperty("end_date", date("Y-m-d", strtotime("+" . $dateDiff->days . " days")));
        }

        $voucher->SetProperty("voucher_date", date("Y-m-d"));
        $voucher->RemoveProperty("recurring");
        $voucher->RemoveProperty("voucher_id");
        $voucher->SetProperty("IsVoucherCron", 1);
        $voucher->SetProperty("created_user_id", DAUERGUTSCHEINE);
        if (!$voucher->Save()) {
            continue;
        }

        $countVoucherUpdate++;

        if ($voucher->GetProperty("group_id") != ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
            continue;
        }

        $countFoodVoucher++;
    }

    $employeeVoucherList->LoadVoucherListByEmployeeID($employeeID, false, false, true);

    $countVoucherFound += count($employeeVoucherList->GetItems());

    foreach ($employeeVoucherList->GetItems() as $employeeVoucher) {
        $voucher->LoadByID($employeeVoucher["voucher_id"]);
        if (!$voucher->GenerateVoucherAndSendToEmail($employee)) {
            continue;
        }

        $countSent++;

        if ($voucher->GetProperty("group_id") != ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
            continue;
        }

        $countFoodVoucher++;
    }

    if ($countFoodVoucher > 0) {
        $specificProductGroup = SpecificProductGroupFactory::Create(ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER));
        $unit = $specificProductGroup->GetUnit(new Receipt(
            "receipt",
            array(
                "document_date" => GetCurrentDate(),
                "employee_id" => $employeeID
            )
        ), "admin");

        $voucher->SetProperty("amount", $unit);
        $voucher->SendVoucherToEmail(
            $employee,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
            $countFoodVoucher
        );
    }

    $totalCountFoodVoucher += $countFoodVoucher;

    if (!$voucher->HasErrors()) {
        continue;
    }

    $errorList .= "Employee ID " . $employeeID . ": " . $voucher->GetErrorsAsString("</br>") . "</br>";
}
$countTotalSend = $totalCountFoodVoucher + $countSent;
$cronLog .= "Updated $countVoucherUpdate vouchers.</br>";
$cronLog .= "Found $countVoucherFound vouchers. Generated and sent $countTotalSend of them.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
