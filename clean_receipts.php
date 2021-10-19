<?php

require_once(dirname(__FILE__) . "/include/init.php");

$receiptList = new ReceiptList("receipt");
$request = new LocalObject(array("FilterEmployeeID" => 1003));
$receiptList->LoadReceiptListForAdmin($request, true);

$receipt = new Receipt("receipt");

foreach ($receiptList->GetItems() as $value) {
    $receipt->LoadByID($value["receipt_id"]);
    $receipt->RemoveReceiptData();
}
