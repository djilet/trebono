<?php

$user->LoadBySession();
if (!$user->Validate(array("root"))) {
    $user->ValidateAccess(array("invoice" => null));
} else {
    $isAdmin = true;
}

$navigation[] = array(
    "Title" => GetTranslation("section-invoice", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array(
    "FilterCreatedRange",
    "FilterStatus",
    "FilterTitle",
    "ItemsOnPageInvoice",
    "ItemsOnPageExportInvoice"
);

if (!Invoice::ValidateAccess($request->GetProperty("invoice_id"))) {
    Send403();
}

$urlFilter->AppendFromObject($request, $filterParams);

$header = array(
    "Title" => GetTranslation("section-invoice", $module),
    "Navigation" => $navigation,
    "StyleSheets" => array(
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"),
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
    ),
    "JavaScripts" => array(
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"),
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"),
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
    )
);

$content = $adminPage->Load("invoice_list.html", $header);

if ($request->GetProperty("Action") == "GetInvoicePDF") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "invoice_id", $request->GetProperty("invoice_id"));

    $invoice = new Invoice($module);
    $invoice->LoadByID($request->GetProperty("invoice_id"));

    $companyUnit = new CompanyUnit("company");
    $companyUnit->LoadByID($invoice->GetProperty("company_unit_id"));

    $fileName = date(
        "ym",
        strtotime($invoice->GetProperty("created"))
    ) . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $invoice->GetProperty("invoice_guid") . ".pdf";
    $archive = $request->GetProperty("Archive") ?? $invoice->GetProperty("archive");
    $filePath = $archive == "Y" ? INVOICE_ARCHIVE_DIR . $fileName : INVOICE_DIR . $fileName;

    OutputFile($filePath, CONTAINER__BILLING__INVOICE, $fileName);
} elseif ($request->GetProperty("Action") == "GetExportInvoiceCSV") {
    $exportInvoice = new ExportInvoice($module);
    $exportInvoice->LoadByID($request->GetProperty("export_id"));
    $filename = "extf_buchungsstapel_" . date(
        "Ymd",
        strtotime($exportInvoice->GetProperty("created"))
    ) . "_" . $exportInvoice->GetProperty("export_number") . ".csv";
    $filePath = EXPORT_INVOICE_DIR . $filename;

    OutputFile($filePath, CONTAINER__BILLING__EXPORT_INVOICE, $filename, true);
} elseif ($request->GetProperty("Action") == "GetInvoiceDetails") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "details_invoice_id", $request->GetProperty("invoice_id"));

    $invoice = new Invoice($module);
    $invoice->LoadByID($request->GetProperty("invoice_id"));

    $fileName = $invoice->GetProperty("details_file");
    $filePath = INVOICE_DIR . $fileName;

    OutputFile($filePath, CONTAINER__BILLING__INVOICE, $fileName);
} elseif ($request->GetProperty("Action") == "Export") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "invoice_export");

    $invoiceList = new InvoiceList($module);
    $invoiceList->ExportToDatev(
        $request->GetProperty("DateFrom"),
        $request->GetProperty("DateTo")
    );
    $content->SetVar("ActiveTab", 2);
} elseif ($request->GetProperty("Action") == "VoucherExport") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "invoice_voucher_export");

    $invoiceList = new InvoiceList($module);
    $invoiceList->ExportToDatev(
        $request->GetProperty("VoucherDateFrom"),
        $request->GetProperty("VoucherDateTo"),
        null,
        "voucher_invoice"
    );
    $content->SetVar("ActiveTab", 2);
} elseif ($request->GetProperty("Action") == "DeactivateInvoiceExport") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "invoice_export_reset");

    $exportInvoice = new ExportInvoice($module);
    $exportInvoice->DeactivateExportDatev($request->GetProperty("InvoiceExportCreated"));
    $content->SetVar("ActiveTab", 2);
} elseif ($request->GetProperty("Action") == "DeactivateInvoiceVoucherExport") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "invoice_voucher_export_reset");

    $exportInvoice = new ExportInvoice($module);
    $exportInvoice->DeactivateExportDatev($request->GetProperty("InvoiceVoucherExportCreated"), "voucher_invoice");
    $content->SetVar("ActiveTab", 2);
}

$content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));
$content->SetVar("LNG_ActivateMessage", GetTranslation("confirm-activate", "core"));

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "billing", "invoice_list");

//load filter data from session and to session
$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet("Invoice" . $key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty("Invoice" . $key));
    } else {
        $session->SetProperty("Invoice" . $key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

$invoiceList = new InvoiceList($module);

if ($request->GetProperty('Do') == 'Remove' && $request->GetProperty("InvoiceIDs")) {
    $invoiceList->Remove($request->GetProperty("InvoiceIDs"));
    $content->LoadMessagesFromObject($invoiceList);
    $content->LoadErrorsFromObject($invoiceList);
    Operation::Save($link, "billing", "invoice_delete");
}
if ($request->GetProperty('Do') == 'Activate' && $request->GetProperty("InvoiceIDs")) {
    $invoiceList->Activate($request->GetProperty("InvoiceIDs"));
    $content->LoadMessagesFromObject($invoiceList);
    $content->LoadErrorsFromObject($invoiceList);
    Operation::Save($link, "billing", "invoice_activate");
}

$invoiceList->LoadInvoiceListForAdmin($request);
$content->LoadFromObjectList("InvoiceList", $invoiceList);

$exportInvoiceList = new ExportInvoiceList($module);
$exportInvoiceList->LoadExportInvoiceList($request);
$content->LoadFromObjectList("ExportInvoiceList", $exportInvoiceList);

$companyUnitList = new CompanyUnitList($module);
$companyUnitList->LoadCompanyUnitListForTree(null, "invoice");
$content->LoadFromObjectList("CompanyUnitList", $companyUnitList);

$page = $request->GetProperty("Page") ?? 1;

if (!$request->GetProperty("PageInvoice")) {
    $request->SetProperty("PageInvoice", $page);
}

if (!$request->GetProperty("PageExportInvoice")) {
    $request->SetProperty("PageExportInvoice", $page);
}

$pagingInvoiceHTML = $invoiceList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL() .
    "&ActiveTab=1&PageExportInvoice=" . $request->GetProperty("PageExportInvoice"));
$pagingExportInvoiceHTML = $exportInvoiceList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL() .
    "&ActiveTab=2&PageInvoice=" . $request->GetProperty("PageInvoice"));

$content->SetVar("ActiveTab", $request->GetProperty("ActiveTab"));

$content->SetVar("PagingInvoice", $pagingInvoiceHTML);
$content->SetVar("PagingExportInvoice", $pagingExportInvoiceHTML);

$content->SetVar("ListInfoInvoice", GetTranslation(
    'list-info1',
    array('Page' => $invoiceList->GetItemsRange(), 'Total' => $invoiceList->GetCountTotalItems())
));
$content->SetVar("ListInfoExportInvoice", GetTranslation(
    'list-info1',
    array('Page' => $exportInvoiceList->GetItemsRange(), 'Total' => $exportInvoiceList->GetCountTotalItems())
));

$content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));

$content->SetVar("ParamsForItemsOnPageInvoice", urldecode($urlFilter->GetForForm(array("ItemsOnPageInvoice"))));
$content->SetVar(
    "ParamsForItemsOnPageExportInvoice",
    urldecode($urlFilter->GetForForm(array("ItemsOnPageExportInvoice")))
);

$content->LoadFromObject($request, $filterParams);

$itemsOnPageListInvoice = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageListInvoice[] = array("Value" => $v, "Selected" => $v == $invoiceList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageListInvoice", $itemsOnPageListInvoice);

$itemsOnPageListExportInvoice = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageListExportInvoice[] = array(
        "Value" => $v,
        "Selected" => $v == $exportInvoiceList->GetItemsOnPage() ? 1 : 0
    );
}
$content->SetLoop("ItemsOnPageListExportInvoice", $itemsOnPageListExportInvoice);

if (isset($isAdmin)) {
    $content->SetVar("Admin", 1);
}

if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
    $content->SetVar("HistoryAdmin", 'Y');
}
