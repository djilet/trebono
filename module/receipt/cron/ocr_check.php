<?php

/**
 * Find receipt files that couldn't go through OCR due to technical issues with it. Should be runned every 30 minutes.
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");
es_include("ocr/processor.php");

if (Config::GetConfigValue("do_not_use_ocr") == "Y") {
    exit;
}

$module = "receipt";
$type = "receipt_clean";

$receiptList = ReceiptList::GetNoOcrImageReceiptIDs();
if (count($receiptList) > 0) {
    $cronLog = "Started checking photos with OCR after OCR fail.</br>";
    $count = 0;
    $denyCount = 0;
    $deniedReceipts = array();
    $successCount = 0;
    $errorList = "";
    $operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

    foreach ($receiptList as $receipt) {
        $count++;
        $filePath = RECEIPT_IMAGE_DIR . "file/" . $receipt["file_image"];

        $specificProductGroup = SpecificProductGroupFactory::Create($receipt["group_id"]);
        $container = $specificProductGroup->GetContainer();

        $fileStorage = GetFileStorage($container);
        $fileData = $fileStorage->GetFileContent($filePath);

        $fileSys = new FileSys();
        $tmpFilePath = PROJECT_DIR . "var/log/" . $receipt["file_image"];
        $fileSys->PutFileContent($tmpFilePath, $fileData);

        //check if photo contains receipt
        $processor = new OCRProcesor("deu", null, 1);
        $checkResult = $processor->check($tmpFilePath);

        $requestData = $checkResult->requestData;
        $isSuccessful = ($checkResult->status == "fail" ? "N" : "Y");
        $isReceipt = null;
        if ($checkResult->status == "success") {
            $isReceipt = $checkResult->isReceipt ? "Y" : "N";
        }
        OCRProcesor::SaveRequest(
            $requestData["created"],
            $requestData["url"],
            $requestData["response_time"],
            "ocr_1",
            $isSuccessful,
            $receipt["receipt_id"],
            SERVICE_USER_ID,
            $isReceipt
        );

        if ($checkResult->status == "fail") {
            $cronLog .= "Failed to check receipt file " . $receipt["receipt_file_id"] . " (receipt_id is " . $receipt["receipt_id"] . "). Abort checking photos and retry later.</br>";
            $errorList .= $requestData["error_message"] . "</br>";
            Operation::SaveCron($operationID, $cronLog, $type, $errorList);
            break;
        }

        if ($checkResult->status == "error") {
            $cronLog .= "An error occurred while checking receipt file " . $receipt["receipt_file_id"] . " (receipt_id is " . $receipt["receipt_id"] . ").</br>";
            $errorList .= GetTranslation($checkResult->errorCode, $module) . "</br>";
            Operation::SaveCron($operationID, $cronLog, $type, $errorList);
        } else {
            //if photo passed check, mark it
            $stmt = GetStatement(DB_MAIN);
            $query = "UPDATE receipt_file SET needs_check='N' WHERE receipt_file_id=" . Connection::GetSQLString($receipt["receipt_file_id"]);
            $stmt->Execute($query);

            if (!$checkResult->isReceipt) {
                //if photo didn't pass check, deny receipt and write comment about it
                if (!in_array($receipt["legal_receipt_id"], $deniedReceipts) && $receipt["status"] == "new") {
                    Receipt::UpdateField($receipt["receipt_id"], "status", "denied", OCR);
                    Receipt::UpdateField($receipt["receipt_id"], "automatic_processed", "Y", OCR);
                    Receipt::UpdateField(
                        $receipt["receipt_id"],
                        'denial_reason',
                        Connection::GetSQLString(Config::GetConfigValue('receipt_autodeny_not_a_receipt')),
                        OCR
                    );

                    $template = Config::GetConfigValue("message_receipt_autodeny_not_a_receipt");
                    $employee = new Employee($module);
                    $employee->LoadByID($receipt["employee_id"]);
                    $replacements = array(
                        "salutation" => $employee->GetProperty("salutation"),
                        "first_name" => $employee->GetProperty("first_name"),
                        "last_name" => $employee->GetProperty("last_name")
                    );
                    $message = GetLanguage()->ReplacePairs($template, $replacements);

                    $receiptComment = new ReceiptComment($module);
                    $receiptComment->SetProperty("receipt_id", $receipt["receipt_id"]);
                    $receiptComment->SetProperty("user_id", SERVICE_USER_ID);
                    $receiptComment->SetProperty("content", $message);
                    $receiptComment->SetProperty("read_by_admin", "Y");
                    $receiptComment->Create();

                    $replacements = array(
                        "salutation" => $employee->GetProperty("salutation"),
                        "first_name" => $employee->GetProperty("first_name"),
                        "last_name" => $employee->GetProperty("last_name"),
                        "receipt_id" => $receipt["receipt_id"],
                        "legal_receipt_id" => $receipt["legal_receipt_id"],
                        "created" => date("d.m.Y", strtotime($receipt["created"]))
                    );

                    $template = Config::GetConfigValue("push_receipt_denied");
                    $text = GetLanguage()->ReplacePairs($template, $replacements);
                    $data = array(
                        FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_view",
                        "receipt_id" => $receipt["receipt_id"]
                    );

                    Employee::SendPushNotification($receipt["employee_id"], null, $text, $data);

                    $deniedReceipts[] = $receipt["legal_receipt_id"];
                    $denyCount++;
                }
            } else {
                $successCount++;
            }
        }
        @unlink($tmpFilePath);
    }

    $cronLog .= "Found " . count($receiptList) . " receipts with not checked images. Checked $count of them. In total, " . $successCount . " files successfully passed the check and " . $denyCount . " receipts were denied" . (count($deniedReceipts) > 0 ? " (legal ids: " . implode(
        ", ",
        $deniedReceipts
    ) . ")" : "") . ".</br>";
    Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
}
