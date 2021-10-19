<?php

/**
 * Sends push notifications. Should be runned daily.
 */

set_time_limit(60 * 60 * 12);

define("IS_ADMIN", true);
//Set de interface language to force push notification language to be de
$_GET["InterfaceLanguage"] = "de";

require_once(dirname(__FILE__) . "/../../../include/init.php");

$request = new LocalObject(array_merge($_GET, $_POST));

//define what day is it today and what receipts employee should be reminded about
$date = $request->GetProperty("date") ?: GetCurrentDate();

$cronLog = "Started sending out reminder pushes.</br>";
$type = "push_notification";
$operationID = Operation::SaveCron(null, $cronLog, $type);

$totalReceiptCount = 0;
$totalCountSent = 0;
$countEmployees = 0;

// Search for receipts with status "approve_proposed" set X days before
$countSent = 0;
$receiptIDs = ReceiptList::GetApproveProposedReceiptIDsForProcessedNotification($date);
foreach ($receiptIDs as $receiptID) {
    $receipt = new Receipt("receipt");
    $receipt->LoadByID($receiptID);
    $countSent += $receipt->SendReceiptProcessedRemindPushNotification();
}
$cronLog .= "Sent out $countSent push notifications with status \"approve_proposed\" set " . abs(intval(Config::GetConfigValue("push_receipt_processed_remind_after_days"))) . " days before.</br>";
Operation::SaveCron($operationID, $cronLog, $type);
$totalReceiptCount += count($receiptIDs);
$totalCountSent += $countSent;

// Search for companies whose payroll day is coming and notify their employees about "approve_proposed" receipts
$receiptIDs = ReceiptList::GetApproveProposedReceiptIDsForPayrollNotification($date);
$countSent = 0;
foreach ($receiptIDs as $receiptID) {
    $receipt = new Receipt("receipt");
    $receipt->LoadByID($receiptID);
    $countSent += $receipt->SendReceiptPayrollPushNotification();
}
$cronLog .= "Sent out $countSent push notifications for employees from companies, whose payroll day is coming.</br>";
Operation::SaveCron($operationID, $cronLog, $type);
$totalReceiptCount += count($receiptIDs);
$totalCountSent += $countSent;

// Search for expiring benefit receipts
$countSent = 0;
for ($i = 1; $i <= 4; $i++) {
    $receiptIDs = ReceiptList::GetReceiptIDsForExpiringBenefitNotification($date, $i);
    foreach ($receiptIDs as $receiptID) {
        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptID);
        $countSent += $receipt->SendExpiringBenefitReceiptNotification($i);
    }
    $totalReceiptCount += count($receiptIDs);
}
$cronLog .= "Sent out $countSent push notifications for expiring benefit receipts.</br>";
Operation::SaveCron($operationID, $cronLog, $type);
$totalCountSent += $countSent;

// Search for employees who have unused money or units
$dayOfMonth = date('d', strtotime($date));
$daysInMonth = date('t', strtotime($date));
$daysLeft = $daysInMonth - $dayOfMonth;
$statisticData = array();

if ($dayOfMonth == 15 || $dayOfMonth == 24 || $daysLeft == 2) {
    $employeeList = EmployeeList::GetActiveEmployeeIDs();
    $employee = new Employee("company");
    foreach ($employeeList as $employeeID) {
        $employee->LoadByID($employeeID);

        $statisticData[$employeeID] = $data = Statistics::GetStatistics(
            $employee,
            false,
            false,
            [
                "available_units_month",
                "available_month",
                "available_units_month_left",
                "available_month_left",
                "approved_units_month",
                "approved_month",
                "approve_proposed_month",
                "approve_proposed_units_month"
            ]
        );

        $templateFood = Config::GetConfigValue("push_remind_text_food");
        $templateOther = Config::GetConfigValue("push_remind_text");

        foreach ($data["product_groups"] as $key => $productGroup) {
            if ($productGroup["voucher"] == "Y") {
                if ($productGroup["code"] == PRODUCT_GROUP__FOOD_VOUCHER) {
                    $unusedMonth = $productGroup["available_units_month_left"];
                } else {
                    $unusedMonth = $productGroup["available_month_left"];
                }
            } else {
                if ($productGroup["code"] == PRODUCT_GROUP__FOOD) {
                    $approved = $productGroup["approved_units_month"] + $productGroup["approve_proposed_units_month"];
                    $unusedMonth = round($productGroup["available_units_month"] - $approved, 2);
                } else {
                    $approved = $productGroup["approved_month"] + $productGroup["approve_proposed_month"];
                    $unusedMonth = round($productGroup["available_month"] - $approved, 2);
                }
            }

            $template = in_array($productGroup["code"], [PRODUCT_GROUP__FOOD, PRODUCT_GROUP__FOOD_VOUCHER]) ?
                $templateFood : $templateOther;

            if ($unusedMonth <= 0) {
                continue;
            }

            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name"),
                "unused_month" => $unusedMonth,
                "service" => $productGroup["title_translation"],
                "days_left" => $daysLeft
            );
            $text = GetLanguage()->ReplacePairs($template, $replacements);
            $totalCountSent += Employee::SendPushNotification($employeeID, null, $text, array(), array());
            $countEmployees++;
        }
    }
}

if (date("m-d") == "10-01" || date("m-d") == "11-30" || date("m-d") == "12-20") {
    $employeeList = EmployeeList::GetActiveEmployeeIDs();
    $employee = new Employee("company");
    foreach ($employeeList as $employeeID) {
        $employee->LoadByID($employeeID);

        $data = $statisticData[$employeeID] == null
            ? Statistics::GetStatistics($employee, false, false, ["available_month"])
            : $statisticData[$employeeID];

        $template = Config::GetConfigValue("push_remind_text_bonus");

        foreach ($data["product_groups"] as $key => $productGroup) {
            if ($productGroup["code"] != PRODUCT_GROUP__BONUS) {
                continue;
            }

            $specificBonus = new SpecificProductGroupBonus();
            $voucherAvailableMap = $specificBonus->GetAvailableAmountMap(
                $employeeID,
                date('Y-m-d'),
                date('Y-12-12')
            );
            foreach ($voucherAvailableMap as $voucherID => $amount) {
                $voucherAvailableMap[$voucherID] = "#" . $voucherID . " - " . $amount;
            }

            $voucherListStr = implode(", ", $voucherAvailableMap);
            if ($voucherListStr == "") {
                continue;
            }

            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name"),
                "unused_month" => $unusedMonth,
                "voucher_list" => $voucherListStr,
                "service" => $productGroup["title_translation"]
            );
            $text = GetLanguage()->ReplacePairs($template, $replacements);
            $totalCountSent += Employee::SendPushNotification($employeeID, null, $text, array(), array());
            $countEmployees++;
        }
    }
}

$cronLog .= "Found $countEmployees employees with unused money or units.</br>";
Operation::SaveCron($operationID, $cronLog, $type);

$cronLog .= "Starting to count notifications for services with \"number of payment month\"<br>";
Operation::SaveCron($operationID, $cronLog, $type);

//notifications for services with "number of payment month"
$employeeList = EmployeeList::GetActiveEmployeeIDs();
$employee = new Employee("company");
$countServices = 0;
foreach ($employeeList as $employeeID) {
    $employee->LoadByID($employeeID);

    $data = empty($statisticData[$employeeID])
        ? Statistics::GetStatistics($employee, false, false, ["available_month"])
        : $statisticData[$employeeID];

    foreach ($data["product_groups"] as $key => $productGroup) {
        if (round($productGroup["available_month"], 2) <= 0) {
            continue;
        }

        switch ($productGroup["code"]) {
            case PRODUCT_GROUP__AD:
                $paymentType = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__AD__MAIN__RECEIPT_OPTION,
                    $employee->GetProperty("employee_id"),
                    $date
                );
                $monthLimit = $paymentType == "yearly" ? 12 : intval(Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__AD__MAIN__PAYMENT_MONTH_QTY,
                    $employee->GetProperty("employee_id"),
                    $date
                ));
                break;

            case PRODUCT_GROUP__INTERNET:
                $monthLimit = intval(Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__INTERNET__MAIN__PAYMENT_MONTH_QTY,
                    $employee->GetProperty("employee_id"),
                    $date
                ));
                break;

            case PRODUCT_GROUP__MOBILE:
                $monthLimit = intval(Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__MOBILE__MAIN__PAYMENT_MONTH_QTY,
                    $employee->GetProperty("employee_id"),
                    $date
                ));
                break;

            default:
                $monthLimit = 0;
                break;
        }

        if ($monthLimit <= 0) {
            continue;
        }

        $lastReceipt = new Receipt("receipt");
        $where = array();
        $where[] = "employee_id=" . $employee->GetIntProperty('employee_id');
        $where[] = "group_id=" . intval($productGroup["group_id"]);
        $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($date);
        $where[] = "DATE(document_date) + " . $monthLimit . " * INTERVAL '1 month' >= " . Connection::GetSQLDate($date);
        $where[] = "(status='approved' OR status='approve_proposed')";
        $where[] = "archive='N'";
        $query = "SELECT receipt_id, employee_id, group_id, created, amount_approved, real_amount_approved, document_date, DATE(document_date) + " . $monthLimit . " * INTERVAL '1 month' AS end_date
								FROM receipt "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
								ORDER BY document_date DESC
								LIMIT 1";
        $lastReceipt->LoadFromSQL($query);

        $dateDiff = floor((strtotime($lastReceipt->GetProperty("end_date")) - strtotime($date)) / (60 * 60 * 24));
        if (!$lastReceipt->GetProperty("receipt_id") || ($dateDiff != 5 && $dateDiff != 15)) {
            continue;
        }

        $template = $dateDiff == 5
            ? Config::GetConfigValue("push_remind_receipt_expire_5")
            : Config::GetConfigValue("push_remind_receipt_expire_15");

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "service" => $productGroup["title_translation"],
            "days_left" => $dateDiff
        );
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $totalCountSent += Employee::SendPushNotification($employeeID, null, $text);
        $countServices++;
    }
}
$cronLog .= "There were $countServices employees with services with \"number of payment month\".</br>";
Operation::SaveCron($operationID, $cronLog, $type);

// Push and email notifications for expiring vouchers
$services = array(
    array("product_group" => PRODUCT_GROUP__BENEFIT_VOUCHER, "count" => 0, "product_group_name" => " Benefit"),
    array("product_group" => PRODUCT_GROUP__FOOD_VOUCHER, "count" => 0, "product_group_name" => " Food"),
    array("product_group" => PRODUCT_GROUP__GIFT_VOUCHER, "count" => 0, "product_group_name" => " Gift"),
    array("product_group" => PRODUCT_GROUP__BONUS_VOUCHER, "count" => 0, "product_group_name" => " Bonus")
);

$periods = array(
    array("period" => "3_month", "date" => "09-30", "services" => $services, "push" => true, "email" => true),
    array("period" => "2_month", "date" => "10-31", "services" => $services, "push" => true, "email" => true),
    array("period" => "1_month", "date" => "11-30", "services" => $services, "push" => true, "email" => true),
    array("period" => "14_day", "date" => "12-14", "services" => $services, "push" => true, "email" => true),
    array("period" => "7_day", "date" => "12-21", "services" => $services, "push" => true, "email" => true),
    array("period" => "expired", "date" => "01-01", "services" => $services, "push" => false, "email" => true)
);

$cronLog .= "Starting to send out notifications for expiring vouchers in Benefit Voucher, Food Voucher, Gift Voucher, Bonus Voucher Services.</br>";
Operation::SaveCron($operationID, $cronLog, $type);

$endDate = date("Y-12-31", strtotime($date));
$currentDate = date("m-d", strtotime($date));
$dates = array_column($periods, "date");

if (in_array($currentDate, $dates)) {
    $employeeList = EmployeeList::GetActiveEmployeeIDs();
    $employee = new Employee("company");

    $index = array_search($currentDate, $dates);

    foreach ($employeeList as $employeeID) {
        for ($j = 0; $j < count($periods[$index]["services"]); $j++) {
            $employee->LoadByID($employeeID);

            $voucherList = new VoucherList("company");
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode($periods[$index]["services"][$j]["product_group"]),
                false,
                false,
                true,
                true,
                $endDate,
                "common"
            );

            if ($voucherList->GetCountItems() <= 0) {
                continue;
            }

            //send push
            if ($periods[$index]["push"]) {
                $voucherListText = "";

                foreach ($voucherList->_items as $voucher) {
                    if ($voucher["amount_left"] <= 0) {
                        continue;
                    }

                    if (
                        $periods[$index]["services"][$j]["product_group"] == PRODUCT_GROUP__FOOD_VOUCHER ||
                        $periods[$index]["services"][$j]["product_group"] == PRODUCT_GROUP__GIFT_VOUCHER
                    ) {
                        $voucherListText .= $voucher["voucher_id"] . "  " . $voucher["amount_left"] . "; ";
                    } elseif ($periods[$index]["services"][$j]["product_group"] == PRODUCT_GROUP__BENEFIT_VOUCHER) {
                        $voucherListText .= $voucher["voucher_id"] . "  " . $voucher["amount_left"] . "(" . $voucher["reason"] . "); ";
                    }
                }

                $template = Config::GetConfigValue("push_remind_voucher_service_expire_" . $periods[$index]["period"]);

                $replacements = array(
                    "salutation" => $employee->GetProperty("salutation"),
                    "first_name" => $employee->GetProperty("first_name"),
                    "last_name" => $employee->GetProperty("last_name"),
                    "product_name" => GetTranslation(
                        "product-group-" . $periods[$index]["services"][$j]["product_group"],
                        "product"
                    ),
                    "voucher_list" => $voucherListText
                );
                $text = GetLanguage()->ReplacePairs($template, $replacements);
                $totalCountSent += Employee::SendPushNotification($employeeID, null, $text);
            }

            //send email
            if (!$periods[$index]["email"]) {
                continue;
            }

            $voucherListExpired = array();
            $voucherListExpiredResult = array();

            foreach ($voucherList->_items as $voucher) {
                if ($voucher["amount_left"] <= 0) {
                    continue;
                }

                $periods[$index]["services"][$j]["count"] += 1;

                if (isset($voucherListExpired[$voucher["amount_left"]])) {
                    $voucherListExpired[$voucher["amount_left"]]["count"] += 1;
                } else {
                    $voucherListExpired[$voucher["amount_left"]]["count"] = 1;
                }

                $voucherListExpired[$voucher["amount_left"]]["voucher_ids"][] = $voucher["voucher_id"];
            }

            foreach ($voucherListExpired as $amount => $vouchers) {
                $voucherListExpiredResult[] = array(
                    "open_amount" => $amount,
                    "count" => $vouchers["count"],
                    "voucher_ids" => implode(", ", $vouchers["voucher_ids"]),
                    "product_name" => GetTranslation(
                        "product-group-" . $periods[$index]["services"][$j]["product_group"],
                        "product"
                    )
                );
            }

            $emailTemplate = new PopupPage("company");
            $attachments = array();

            if ($periods[$index]["period"] == "expired") {
                $tmpl = $emailTemplate->Load("voucher_expired_notification_email.html");
                $subject = "Ihre Gutscheine sind am " . $endDate . " abgelaufen";
            } else {
                $tmpl = $emailTemplate->Load("voucher_notification_email.html");
                $subject = "Ihre Gutscheine laufen zum " . $endDate . " aus";
            }

            $tmpl->SetLoop("voucher_list", $voucherListExpiredResult);
            $tmpl->SetVar("end_date", $endDate);
            $tmpl->LoadFromObject($employee);

            SendMailFromAdmin(
                $employee->GetProperty("email"),
                $subject,
                $emailTemplate->Grab($tmpl),
                array(),
                array(
                    array(
                        "Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png",
                        "CID" => "logo"
                    )
                ),
                $attachments
            );
        }
    }
}

$cronLog .= "Found ";
for ($i = 0; $i < count($services); $i++) {
    foreach ($periods as $period) {
        $cronLog .= $period["services"][$i]["count"] . " " . $services[$i]["product_group_name"] . " vouchers that expire in " . $period["period"] . ".</br>";
    }
}

Operation::SaveCron($operationID, $cronLog, $type);

$cronLog .= "In total, found " . $totalReceiptCount . " receipts that should had been reminded of. " . $totalCountSent . " push notifications were sent.</br>";
Operation::SaveCron($operationID, $cronLog, $type, null, null, true);
