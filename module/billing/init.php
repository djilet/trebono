<?php

define("INVOICE_LINE_TYPE_RECURRING", "recurring");
define("INVOICE_LINE_TYPE_IMPLEMENTATION", "implementation");
define("INVOICE_LINE_TYPE_BILL", "bill");
define("INVOICE_STATUS_NEW", "new");
define("INVOICE_STATUS_SENT", "sent");
define("INVOICE_STATUS_ERROR", "error");
define("INVOICE_STATUS_PAID", "paid");
define("CONTAINER__BILLING__INVOICE", "");
define("CONTAINER__BILLING__PAYROLL", "");
define("CONTAINER__BILLING__STORED_DATA", "");
define("CONTAINER__BILLING__MASTER_DATA", "");
define("CONTAINER__BILLING__VOUCHER_EXPORT", "");
define("CONTAINER__BILLING__EXPORT_INVOICE", "");
define("CONTAINER__BILLING__BOOKKEEPING_EXPORT", "");

$GLOBALS['moduleConfig']['billing'] = array(
    'ColorA' => '#000',
    'ColorI' => '#000',
    'Config' => array()
);
