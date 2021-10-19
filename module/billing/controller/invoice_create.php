<?php

/**
 * Temporary script for manual invoice creation
 */

$user->ValidateAccess(array("root"));

$invoice = new Invoice($module);
$invoice->LoadFromArray(array(
    "company_unit_id" => 26,
    "date_from" => "2018-08-01",
    "date_to" => "2018-08-31",
));
$invoice->Create();

$lineDataList = array(
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "6",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "15"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "1",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "15"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "1",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "25"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "2",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "9"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "2",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "9"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "3",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "11"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "3",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "11"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "4",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "5"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "4",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "5"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "5",
        "type" => INVOICE_LINE_TYPE_RECURRING,
        "quantity" => "50"
    ),
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "5",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "50"
    )
,
    array(
        "invoice_id" => $invoice->GetProperty("invoice_id"),
        "product_id" => "7",
        "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
        "quantity" => "50"
    )
);

foreach ($lineDataList as $data) {
    $invoiceLine = new InvoiceLine($module);
    $invoiceLine->LoadFromArray($data);
    $invoiceLine->Create();
}
