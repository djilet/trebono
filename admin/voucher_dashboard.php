<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

if (!$request->GetProperty("Filter")) {
    $request->SetProperty("yearly_statistics_date", date("Y-01-01"));
}

$sectionList = ["statistics", "reports", "vat_report"];
if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", $sectionList[0]);
}

$templateSectionList = [];
foreach ($sectionList as $section) {
    $sectionTitle = GetTranslation("voucher-dashboard-section-" . $section);
    $templateSectionList[] = [
        "Section" => $section,
        "Title" => $sectionTitle,
        "Selected" => ($request->GetProperty("Section") == $section ? 1 : 0),
    ];
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request);

$adminPage = new AdminPage();

$navigation = [
    ["Title" => GetTranslation("title-voucher_dashboard"), "Link" => "voucher_dashboard.php"],
];
$header = [
    "Title" => GetTranslation("title-voucher_dashboard"),
    "Navigation" => $navigation,
    "StyleSheets" => [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/jquery-ui/smoothness/jquery-ui.min.css"],
    ],
    "JavaScripts" => [
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-ui/smoothness/jquery-ui.min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
    ],
];

if ($request->GetProperty("Do") == "ShowDetails" && $request->IsPropertySet("group_id")) {
    $content = $adminPage->Load("voucher_dashboard_details.html", $header);

    $year = $request->GetProperty("yearly_statistics_date");
    $startOfYear = date("Y-01-01", strtotime("01/01/" . $year));
    $endOfYear = date("Y-12-31", strtotime("01/01/" . $year));

    $voucherListForStatistics = VoucherList::GetVoucherDashboardAmount(
        true,
        $request->GetProperty("group_id"),
        $startOfYear,
        $endOfYear,
        "open"
    );

    $voucherListForStatistics = $request->GetProperty("type") == "not_used"
        ? $voucherListForStatistics["not_used_list"]
        : $voucherListForStatistics["partially_used_list"];

    $voucherList = new VoucherList("company");
    $voucherStatistics = $voucherList->GetVoucherDashboardDetails(
        $voucherListForStatistics,
        $request->GetProperty("yearly_statistics_date"),
        $content->GetVar("INTERFACE_LANGCODE")
    );
    $content->SetLoop("MonthTitleList", $voucherStatistics["month_title_list"]);
    $content->SetLoop("StatisticsList", $voucherStatistics["voucher_list"]);
} elseif ($request->GetProperty("Do") == "ShowVatDetails") {
    $content = $adminPage->Load("voucher_dashboard_vat_details.html", $header);
    if ($request->IsPropertySet("voucher_type")) {
        $request->SetProperty("voucher_type", explode(",", $request->GetProperty("voucher_type")));
    }
    $receiptList = ReceiptList::GetVatReport($request, true);
    $content->SetLoop("ReceiptList", $receiptList[$request->GetProperty("type")]);
    $content->SetVar("TotalSum", $receiptList[$request->GetProperty("type") . "_total"]);
} else {
    if (!$request->GetProperty("Filter")) {
        $request->SetProperty("yearly_statistics_date", date("Y"));
    }

    $content = $adminPage->Load("voucher_dashboard.html", $header);

    $link = "voucher_dashboard.php";
    Operation::Save($link, "dashboard", "dashboard");

    $request->SetProperty("LanguageCode", $content->GetVar("INTERFACE_LANGCODE"));
    $productGroupList = ProductGroupList::GetProductGroupList(false, false, false, true);
    foreach ($productGroupList as $key => $productGroup) {
        $productGroupList[$key]["title_translation"] =
            GetTranslation("product-group-" . $productGroup["code"], "product");
        if (!in_array($productGroup["group_id"], $request->GetProperty("voucher_type"))) {
            continue;
        }

        $productGroupList[$key]["selected"] = true;
    }
    $content->SetVar("VoucherTypeFilter", implode(",", $request->GetProperty("voucher_type")));
    $content->SetLoop("VoucherProductGroup", $productGroupList);

    $dashboard = new Dashboard();

    if ($request->GetProperty("Section") == "statistics") {
        $dashboard->LoadVoucherDashboard($request);
    } elseif ($request->GetProperty("Section") == "vat_report") {
        $dashboard->LoadVatReport($request);
    } elseif ($request->GetProperty("Section") == "reports") {
        $companyList = new CompanyUnitList("company");
        $companyList->LoadCompanyUnitListForTree();
        $companyListHTML = "";
        foreach ($companyList->GetItems() as $item) {
            if ($request->GetProperty("company_unit_id") != null && in_array($item['company_unit_id'], $request->GetProperty("company_unit_id"))) {
                $companyListHTML .= "<option selected value='" . $item['company_unit_id'] . "' data-title='" . $item['title'] . "'>" . $item['select_prefix'] . $item['title'] . ", " . GetTranslation("remove-company-unit-id") . " " . $item['company_unit_id'] . "</option>";
            } else {
                $companyListHTML .= "<option value='" . $item['company_unit_id'] . "' data-title='" . $item['title'] . "'>" . $item['select_prefix'] . $item['title'] . ", " . GetTranslation("remove-company-unit-id") . " " . $item['company_unit_id'] . "</option>";
            }
        }
        $content->SetVar("CompanyListHTML", $companyListHTML);

        $dashboard->LoadVoucherReports($request);
    }

    $content->LoadFromObject($dashboard);
    $content->LoadFromObject($request);
}

$content->LoadFromObject($request);
$content->SetVar("ParamsForFilter", $urlFilter->GetForForm());
$content->SetVar("Section", $request->GetProperty("Section"));
$content->SetLoop("VoucherDashboardSectionList", $templateSectionList);

$adminPage->Output($content);
