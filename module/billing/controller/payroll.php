<?php

$user->LoadBySession();
if (!$user->Validate(array("root"))) {
    $user->ValidateAccess(["payroll" => null, "tax_auditor" => null], "or");
} else {
    $isAdmin = true;
}

$navigation[] = array(
    "Title" => GetTranslation("section-payroll", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("FilterCreatedRange", "FilterTitle", "ItemsOnPagePayroll", "ItemsOnPageVoucher");

$urlFilter->AppendFromObject($request, $filterParams);

if (!Payroll::ValidateAccess($request->GetProperty("payroll_id"))) {
    Send403();
}

$link = $moduleURL . "&" . $urlFilter->GetForURL();

if ($request->GetProperty("Action") == "GetPayrollPDF") {
    Operation::Save($link, "billing", "payroll_id", $request->GetProperty("payroll_id"));

    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("pdf_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName);
} elseif ($request->GetProperty("Action") == "GetPayrollLug") {
    Operation::Save($link, "billing", "payroll_id", $request->GetProperty("payroll_id"));

    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("lug_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName, true);
} elseif ($request->GetProperty("Action") == "GetPayrollLodas") {
    Operation::Save($link, "billing", "payroll_id", $request->GetProperty("payroll_id"));

    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("lodas_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, "imp_lbw.txt", true);
} elseif ($request->GetProperty("Action") == "GetPayrollLogga") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("logga_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName, true);
} elseif ($request->GetProperty("Action") == "GetPayrollTopas") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));
    $fileName = $payroll->GetProperty("topas_file");
    $payrollMonth = substr($payroll->GetProperty("payroll_month"), -2);
    $clientID = substr(
        CompanyUnit::GetPropertyValue("client_id", $payroll->GetProperty("company_unit_id")),
        -4
    ); //last 4 digits of customerID
    $newFileName = $payrollMonth . $clientID . ".csv";
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName, true, $newFileName);
} elseif ($request->GetProperty("Action") == "GetPayrollAddison") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("addison_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, "imp_lbw.txt", true);
} elseif ($request->GetProperty("Action") == "GetPayrollLexware") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("lexware_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile(
        $filePath,
        CONTAINER__BILLING__PAYROLL,
        "Lex_" . $payroll->GetProperty("company_unit_id") . ".txt",
        true
    );
} elseif ($request->GetProperty("Action") == "GetPayrollPerforce") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("perforce_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName, true);
} elseif ($request->GetProperty("Action") == "GetPayrollSage") {
    $payroll = new Payroll($module);
    $payroll->LoadByID($request->GetProperty("payroll_id"));

    $fileName = $payroll->GetProperty("sage_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName, true);
} elseif ($request->GetProperty("Action") == "GetVoucherExport") {
    $voucherExport = new VoucherExport($module);
    $voucherExport->LoadByID($request->GetProperty("export_id"));

    $filename = "extf_buchungsstapel_creditor_" .
        date_create($voucherExport->GetProperty("created"))->format("Ymd") . "_" .
        $voucherExport->GetProperty("export_number") . ".csv";
    $filePath = VOUCHER_EXPORT_DIR . $filename;

    OutputFile($filePath, CONTAINER__BILLING__VOUCHER_EXPORT, $filename, true);
}

$header = array(
    "Title" => GetTranslation("section-payroll", $module),
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

$content = $adminPage->Load("payroll_list.html", $header);

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "billing", "payroll_list");

//load filter data from session and to session
$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet("Payroll" . $key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty("Payroll" . $key));
    } else {
        $session->SetProperty("Payroll" . $key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

if ($request->GetProperty("Action") == "Export") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "payroll_voucher_export");

    $voucherList = new VoucherList($module);
    $voucherList->ExportToDatev(null, $request->GetProperty("date"));
    $request->SetProperty("ActiveTab", 2);
}
if ($request->GetProperty("Action") == "ExportReset") {
    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "billing", "payroll_voucher_export_reset");

    $voucherExport = new VoucherExport($module);
    $voucherExport->ResetExport($request->GetProperty("date"));
}

$page = $request->GetProperty("Page") ?? 1;

if (!$request->GetProperty("PagePayroll")) {
    $request->SetProperty("PagePayroll", $page);
}

if (!$request->GetProperty("PageVoucher")) {
    $request->SetProperty("PageVoucher", $page);
}

$voucherExportList = new VoucherExportList($module);
$voucherExportList->LoadVoucherExportList($request);

$content->LoadFromObjectList("VoucherExportList", $voucherExportList);

$payrollList = new PayrollList($module);
$payrollList->LoadPayrollList($request);

$content->LoadFromObjectList("PayrollList", $payrollList);

$companyUnitList = new CompanyUnitList($module);
$companyUnitList->LoadCompanyUnitListForTree(
    null, ["payroll", "tax_auditor"], null, null, null, null, "or"
);
$content->LoadFromObjectList("CompanyUnitList", $companyUnitList);

$content->SetVar("ShowCreateBlock", 1);

$pagingPayrollHTML = $payrollList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL() .
    "&ActiveTab=1&PageVoucher=" . $request->GetProperty("PageVoucher"));
$pagingVoucherHTML = $voucherExportList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL() .
    "&ActiveTab=2&PagePayroll=" . $request->GetProperty("PagePayroll"));

$content->SetVar("PagingPayroll", $pagingPayrollHTML);
$content->SetVar("PagingVoucher", $pagingVoucherHTML);

$content->SetVar("ListInfoPayroll", GetTranslation(
    'list-info1',
    array('Page' => $payrollList->GetItemsRange(), 'Total' => $payrollList->GetCountTotalItems())
));
$content->SetVar("ListInfoVoucher", GetTranslation(
    'list-info1',
    array('Page' => $voucherExportList->GetItemsRange(), 'Total' => $voucherExportList->GetCountTotalItems())
));

$content->SetVar("ParamsForItemsOnPagePayroll", urldecode($urlFilter->GetForForm(array("ItemsOnPagePayroll"))));
$content->SetVar("ParamsForItemsOnPageVoucher", urldecode($urlFilter->GetForForm(array("ItemsOnPageVoucher"))));

$content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));

$content->SetVar("ActiveTab", $request->GetProperty("ActiveTab"));

$content->LoadFromObject($request, $filterParams);

$itemsOnPageListPayroll = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageListPayroll[] = array("Value" => $v, "Selected" => $v == $payrollList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageListPayroll", $itemsOnPageListPayroll);

$itemsOnPageListVoucher = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageListVoucher[] = array("Value" => $v, "Selected" => $v == $voucherExportList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageListVoucher", $itemsOnPageListVoucher);

if (isset($isAdmin)) {
    $content->SetVar("Admin", 1);
}

if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
    $content->SetVar("HistoryAdmin", 'Y');
}
