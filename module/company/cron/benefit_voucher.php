<?php

/**
 * Generate bvs vouchers. Should be run every day
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");
$request = new LocalObject(array_merge($_GET, $_POST));

$module = "company";
$moduleProduct = "product";
$type = "voucher";

$date = $request->GetProperty("date") ?: GetCurrentDate();

$cronLog = "Started creating benefit vouchers.</br>";
$countVoucher = 0;
$errorList = "";
$usedIDs = array();
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);
$benefitMainProductID = Product::GetProductIDByCode(PRODUCT__BENEFIT_VOUCHER__MAIN);
$benefitProductGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER);

$employeeList = array();
if ($request->GetProperty("employee_id")) {
    $optionValue = Option::GetInheritableOptionValue(
        OPTION_LEVEL_EMPLOYEE,
        OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION,
        $request->GetProperty("employee_id"),
        $date
    );
    if ($optionValue == "Y") {
        $employeeList[] = $request->GetProperty("employee_id");
    }
} else {
    $employeeList = EmployeeList::GetEmployeeIDsForGenerationVouchers($benefitProductGroupID);
}

foreach ($employeeList as $employeeID) {
    $contract = new Contract($moduleProduct);
    $voucher = new Voucher($module);
    if ($voucher->VoucherExist(PRODUCT_GROUP__BENEFIT_VOUCHER, $employeeID, $date)) {
        continue;
    }

    $voucher->SetProperty("employee_id", $employeeID);

    $contract->LoadLatestActiveContract(OPTION_LEVEL_EMPLOYEE, $employeeID, $benefitMainProductID);
    if (
        date("m", strtotime($contract->GetProperty("start_date"))) == date(
            "m",
            strtotime($date)
        ) && strtotime($contract->GetProperty("start_date")) > strtotime(date(
            "1.m.Y",
            strtotime($date)
        ))
    ) {
        $voucher->SetProperty("voucher_date", $contract->GetProperty("start_date"));
    } else {
        $voucher->SetProperty("voucher_date", date("1.m.Y", strtotime($date)));
    }

    $voucher->SetProperty("end_date", date("31.12.Y", strtotime($date . "+ 3 year")));

    $setsOfGood = Voucher::GetVoucherReasonList(
        Voucher::GetDefaultVoucherReason(
            OPTION_LEVEL_EMPLOYEE,
            $voucher->GetProperty("employee_id"),
            PRODUCT_GROUP__BENEFIT_VOUCHER,
            $voucher->GetProperty("voucher_date")
        ),
        "voucher_sets_of_goods"
    );
    foreach ($setsOfGood as $reason) {
        if (!$reason["Selected"]) {
            continue;
        }

        $voucher->SetProperty("reason", $reason["Reason"]);
    }

    $maxMonthly = Option::GetInheritableOptionValue(
        OPTION_LEVEL_EMPLOYEE,
        OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY,
        $employeeID,
        $date
    );
    $voucher->SetProperty("amount", $maxMonthly);
    $voucher->SetProperty("group_id", $benefitProductGroupID);
    $voucher->SetProperty("IsBenefitVoucherCron", 1);
    $voucher->SetProperty("created_user_id", SB_GUTSCHEINE);

    if ($voucher->Save()) {
        $countVoucher++;
        $usedIDs[] = ["employee_id" => $employeeID, "name" => Employee::GetNameByID($employeeID)];
    }
    if (!$voucher->HasErrors()) {
        continue;
    }

    $errorList .= "Employee ID " . $employeeID . ": " . $voucher->GetErrorsAsString("</br>") . "</br>";
}

$cronLog .= "Created " . $countVoucher . " benefit vouchers.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, $usedIDs, true);
