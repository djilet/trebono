<?php

/**
 * Creates new invoices. Should be runned monthly.
 */

set_time_limit(60 * 60 * 3);

require_once(dirname(__FILE__) . "/../../../include/init.php");
require_once(dirname(__FILE__) . "/../init.php");

$module = "billing";
$request = new LocalObject(array_merge($_GET, $_POST));

$dayOfMonth = date('d');
$date = $request->GetProperty("date") ?? date("Y-m-d");

$cronLog = "Started creating benefit voucher invoices for $date.</br>";
$type = "invoice_create";
$countCreated = 0;
$countSent = 0;
$errorList = "";
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

$voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
$voucherProductGroupList = array_column($voucherProductGroupList, "code");
$voucherProductList = ProductList::GetVoucherProductList(true);
$voucherProductList = array_column($voucherProductList, "code");

//$conditionList = InvoiceHelper::GetInvoiceCreationCompanyUnitConditionList($date);
$companyUnitIDs = CompanyUnitList::GetCompanyUnitIDsForInvoiceCreation($date, "voucher_invoice");
if ($request->GetProperty("company_unit_id")) {
    $companyUnitIDs = in_array($request->GetProperty("company_unit_id"), $companyUnitIDs)
        ? array($request->GetProperty("company_unit_id"))
        : array();
}

$cronLog .= "Invoices are being created for " . count($companyUnitIDs) . " company units.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList);
$invoiceType = "voucher_invoice";

foreach ($companyUnitIDs as $companyUnitID) {
    //load company unit and generate its invoice period
    $companyUnit = new CompanyUnit("company");
    $companyUnit->LoadByID($companyUnitID);
    if ($companyUnit->GetProperty("title") == "Apple Test Account") {
        continue;
    }

    $periodAfter = InvoiceHelper::GetInvoicePeriodAfter($date, $companyUnit->GetProperty("payment_type"));
    $periodBefore = InvoiceHelper::GetInvoicePeriodBefore($date, $companyUnit->GetProperty("payment_type"));
    if ($periodAfter === null && $periodBefore === null) {
        continue;
    }
    [$dateFromPeriodBefore, $dateToPeriodBefore] = $periodBefore;
    [$dateFromPeriodAfter, $dateToPeriodAfter] = $periodAfter;

    $subUnitIDs = CompanyUnitList::GetAllCompanyUnitIDs($companyUnit->GetProperty("company_id"));
    $subUnitLines = array();
    $subUnitDetails = array();
    $periodBeforeLines = 0;
    $periodAfterLines = 0;
    foreach ($subUnitIDs as $subUnitID) {
        $subUnit = new CompanyUnit("company");
        $subUnit->LoadByID($subUnitID);

        //iterate the products and collect their recurring and implementation invoice data
        $lineDataListPeriodBefore = array();
        $lineDataListPeriodAfter = array();

        $invoiceDetailsPeriodBefore = array();
        $invoiceDetailsPeriodAfter = array();

        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
            if (!in_array($productGroupList->_items[$i]["code"], $voucherProductGroupList)) {
                continue;
            }

            $productList = new ProductList("product");
            $productList->LoadProductListForAdmin($productGroupList->_items[$i]["group_id"]);
            for ($j = 0; $j < $productList->GetCountItems(); $j++) {
                if (!in_array($productList->_items[$j]["code"], $voucherProductList)) {
                    continue;
                }

                $specificProduct = SpecificProductFactory::Create($productList->_items[$j]["code"]);

                $recurringLineData = $specificProduct->GetRecurringVoucherInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    null,
                    $dateToPeriodBefore,
                    $productGroupList->_items[$i]["group_id"],
                    null,
                    false,
                    $date
                );
                $recurringInvoiceDetails = $recurringLineData["invoice_details"];
                $recurringLineData = $recurringLineData["line_data"];
                if ($recurringLineData !== null) {
                    $lineDataListPeriodBefore[] = $recurringLineData;
                    $invoiceDetailsPeriodBefore[] = $recurringInvoiceDetails;
                }

                $recurringLineData = $specificProduct->GetRecurringVoucherInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodAfter,
                    $dateToPeriodAfter,
                    $productGroupList->_items[$i]["group_id"],
                    null,
                    false,
                    $date
                );
                $recurringInvoiceDetails = $recurringLineData["invoice_details"];
                $recurringLineData = $recurringLineData["line_data"];
                if ($recurringLineData !== null) {
                    $lineDataListPeriodAfter[] = $recurringLineData;
                }
                $invoiceDetailsPeriodAfter[] = $recurringInvoiceDetails;
            }
        }
        $subUnitLines[$subUnitID] = array($lineDataListPeriodBefore, $lineDataListPeriodAfter);
        $subUnitDetails[$subUnitID] = array($invoiceDetailsPeriodBefore, $invoiceDetailsPeriodAfter);
        $periodBeforeLines += count($lineDataListPeriodBefore);
        $periodAfterLines += count($lineDataListPeriodAfter);
    }
//  create and send invoice if there are items to pay for
    if ($periodBeforeLines > 0) {
        $invoice = new Invoice($module);
        $invoice->LoadFromArray(array(
            "company_unit_id" => $companyUnitID,
            "date_from" => $dateFromPeriodBefore,
            "date_to" => $dateToPeriodBefore,
            "is_cron" => true,
            "invoice_type" => $invoiceType
        ));
        if ($invoice->Create()) {
            $countCreated++;
        } else {
            $errorList .= $invoice->GetErrorsAsString("</br>");
        }

        for ($i = 0; $i < count($subUnitIDs); $i++) {
            foreach ($subUnitLines[$subUnitIDs[$i]][0] as $companyLineData) {
                foreach ($companyLineData as $lineData) {
                    $invoiceLine = new InvoiceLine($module);
                    $invoiceLine->LoadFromArray($lineData);
                    $invoiceLine->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
                    $invoiceLine->Create();
                    if (count($lineData["voucher_ids"]) <= 0) {
                        continue;
                    }

                    $stmt = GetStatement(DB_MAIN);
                    $query = "UPDATE voucher SET invoice_export_id=" . Connection::GetSQLString($invoice->GetProperty("invoice_id")) . " WHERE voucher_id IN (" . implode(
                        ", ",
                        $lineData["voucher_ids"]
                    ) . ")";
                    $stmt->Execute($query);
                }
            }

            foreach ($subUnitDetails[$subUnitIDs[$i]][0] as $detailsList) {
                foreach ($detailsList as $detailsData) {
                    $invoiceDetails = new InvoiceDetails($module);
                    $invoiceDetails->LoadFromArray($detailsData);
                    $invoiceDetails->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
                    $invoiceDetails->SetProperty("invoice_type", $invoiceType);
                    $invoiceDetails->Create();
                }
            }
        }

        if ($invoice->Send($invoice->GetProperty("invoice_id"))) {
            $countSent++;
        } else {
            $errorList .= $invoice->GetErrorsAsString("</br>");
        }
    }
    if ($periodAfterLines <= 0) {
        continue;
    }

    $invoice = new Invoice($module);
    $invoice->LoadFromArray(array(
        "company_unit_id" => $companyUnitID,
        "date_from" => $dateFromPeriodAfter,
        "date_to" => $dateToPeriodAfter,
        "is_cron" => true,
        "invoice_type" => $invoiceType
    ));
    if ($invoice->Create()) {
        $countCreated++;
    } else {
        $errorList .= $invoice->GetErrorsAsString("</br>");
    }

    for ($i = 0; $i < count($subUnitIDs); $i++) {
        foreach ($subUnitLines[$subUnitIDs[$i]][1] as $companyLineData) {
            foreach ($companyLineData as $lineData) {
                $invoiceLine = new InvoiceLine($module);
                $invoiceLine->LoadFromArray($lineData);
                $invoiceLine->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
                $invoiceLine->Create();
                if (count($lineData["voucher_ids"]) <= 0) {
                    continue;
                }

                $stmt = GetStatement(DB_MAIN);
                $query = "UPDATE voucher SET invoice_export_id=" . Connection::GetSQLString($invoice->GetProperty("invoice_id")) . " WHERE voucher_id IN (" . implode(
                    ", ",
                    $lineData["voucher_ids"]
                ) . ")";
                $stmt->Execute($query);
            }
        }

        foreach ($subUnitDetails[$subUnitIDs[$i]][1] as $detailsList) {
            foreach ($detailsList as $detailsData) {
                $invoiceDetails = new InvoiceDetails($module);
                $invoiceDetails->LoadFromArray($detailsData);
                $invoiceDetails->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
                $invoiceDetails->SetProperty("invoice_type", $invoiceType);
                $invoiceDetails->Create();
            }
        }
    }

    if ($invoice->Send($invoice->GetProperty("invoice_id"))) {
        $countSent++;
    } else {
        $errorList .= $invoice->GetErrorsAsString("</br>");
    }
}

$cronLog .= "Created $countCreated invoices, $countSent of them were successfully sent.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList, null, true);
