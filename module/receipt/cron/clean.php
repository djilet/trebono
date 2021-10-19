<?php

/**
 * Removes receipts without images. Should be runned every 5 minutes.
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../../include/init.php");

$module = "receipt";
$type = "receipt_clean";

$receiptIDs = ReceiptList::GetNoImageReceiptIDsToRemove();
if (count($receiptIDs) > 0) {
    $cronLog = "Started removing receipts without images.</br>";
    $count = 0;
    $errorList = "";
    $operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

    foreach ($receiptIDs as $receiptID) {
        $receipt = new Receipt($module);
        $receipt->LoadByID($receiptID);
        $result = $receipt->RemoveReceiptData();
        if ($result === true) {
            $count++;
        } else {
            $errorList = $receipt->GetErrorsAsString("</br>");
        }
    }

    $cronLog .= "Found " . count($receiptIDs) . " receipts without images. Removed $count of them.</br>";
    Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
}
