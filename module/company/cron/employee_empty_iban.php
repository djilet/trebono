<?php

/**
 * Push and email notifications for empty iban. Should be runned every night.
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");

$request = new LocalObject(array_merge($_GET, $_POST));
$date = $request->GetProperty("date") ?: GetCurrentDate();

$module = "company";
$type = "push_notification";
$pushCount = 0;
$emailCount = 0;
$errors = [];

$cronLog = "Started sending out notifications about empty iban for employees</br>";;
$operationID = Operation::SaveCron(null, $cronLog, $type);

$emailDays = [5, 15, 20];
$pushDays = [2, 10, 21, 23, 24, 25];
$currentDay = date("d", strtotime($date));

$productIds = [];
$productGroupList = ProductGroupList::GetProductGroupList(false, "Y", false, true);
foreach ($productGroupList as $productGroup) {
    if ($productGroup['group_id'] == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
        $specificProductGroup = SpecificProductGroupFactory::Create($productGroup["group_id"]);
        $productIds[] = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
    }
}

$employeeIds = [];
foreach ($productIds as $productId) {
    $employeeIds = array_merge($employeeIds, EmployeeList::GetActiveEmployeeIDs(false, $productId));
}
$employeeIds = array_unique($employeeIds);

if (count($employeeIds) > 0) {
    $employee = new Employee($module);

    if (in_array($currentDay, $emailDays)) {
        $subject = "Erinnerung: Fehlende Bank Daten";
        $emailTemplate = new PopupPage($module);

        foreach ($employeeIds as $employeeID) {
            $employeePropertyList = $employee->GetCurrentPropertyList($employeeID);

            if (empty(trim($employeePropertyList["iban"]))) {
                $tmpl = $emailTemplate->Load("empty_iban_notification_email.html");
                $tmpl->LoadFromArray($employeePropertyList);
                $result = SendMailFromAdmin(
                    $employeePropertyList["email"],
                    $subject,
                    $emailTemplate->Grab($tmpl),
                    [],
                    [[
                        "Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png",
                        "CID" => "logo"
                    ]],
                    []
                );

                if ($result === true) {
                    $emailCount++;
                } else {
                    $errors[] = $result;
                }
            }
        }
    }

    if (in_array($currentDay, $pushDays)) {
        $messageTmpl = Config::GetConfigValue("push_empty_iban");

        foreach ($employeeIds as $employeeID) {
            $employeePropertyList = $employee->GetCurrentPropertyList($employeeID);

            if (empty(trim($employeePropertyList["iban"]))) {
                $message = GetLanguage()->ReplacePairs($messageTmpl, $employeePropertyList);
                $pushCount += (int)Employee::SendPushNotification($employeeID, null, $message);;
            }
        }
    }
}

$cronLog .= "Sent out " . $emailCount . " emails and " . $pushCount . " push notificaions.</br>";
Operation::SaveCron($operationID, $cronLog, $type, implode("</br>", $errors), null, true);
