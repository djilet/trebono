<?php

$user->ValidateAccess(array("root"));

$navigation[] = array(
    "Title" => GetTranslation("section-invoice-schedule", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$header = array(
    "Title" => GetTranslation("section-invoice-schedule", $module),
    "Navigation" => $navigation,
    "StyleSheets" => array(
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
    ),
    "JavaScripts" => array(
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
    ),
);

$content = $adminPage->Load("invoice_schedule.html", $header);

if ($request->GetProperty("Action") == "Generate") {
    $dateFrom = str_replace(",", "", $request->GetProperty("DateFrom"));
    $dateTo = str_replace(",", "", $request->GetProperty("DateTo"));
    $dateRange = GetDateRange($dateFrom, $dateTo);

    $dateList = array();

    foreach ($dateRange as $date) {
        $conditionList = InvoiceHelper::GetInvoiceCreationCompanyUnitConditionList($date);
        $companyUnitIDs = CompanyUnitList::GetCompanyUnitIDsForInvoiceCreation($conditionList);

        $row = array(
            "date" => $date,
            "company_unit_list" => array()
        );

        foreach ($companyUnitIDs as $companyUnitID) {
            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($companyUnitID);

            [$dateFrom, $dateTo] = InvoiceHelper::GetInvoicePeriod(
                $date,
                $companyUnit->GetProperty("payment_type"),
                $companyUnit->GetProperty("invoice_date")
            );

            $row["CompanyUnitList"][] = array_merge(
                $companyUnit->GetProperties(),
                array("date_from" => $dateFrom, "date_to" => $dateTo)
            );
        }

        $dateList[] = $row;
    }

    $content->SetLoop("DateList", $dateList);
}

$content->LoadFromObject($request);
