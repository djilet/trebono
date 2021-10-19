<?php

/**
 * Creates new invoices. Should be runned daily.
 */

set_time_limit(60 * 60 * 3);

require_once(dirname(__FILE__) . "/../../../include/init.php");
require_once(dirname(__FILE__) . "/../init.php");

$module = "billing";
$request = new LocalObject(array_merge($_GET, $_POST));

//define what day is it today and what companies should get invoices
$date = $request->GetProperty("date") ?: date("Y-m-d");

$cronLog = "Started creating invoices for $date.</br>";
$type = "invoice_create";
$countCreated = 0;
$countSent = 0;
$errorList = "";
$operationID = Operation::SaveCron(null, $cronLog, $type, $errorList);

//$conditionList = InvoiceHelper::GetInvoiceCreationCompanyUnitConditionList($date);
$companyUnitIDs = CompanyUnitList::GetCompanyUnitIDsForInvoiceCreation($date, "invoice");
if ($request->GetProperty("company_unit_id")) {
    $companyUnitIDs = in_array($request->GetProperty("company_unit_id"), $companyUnitIDs)
        ? array($request->GetProperty("company_unit_id"))
        : array();
}

$isCron = $request->GetProperty("is_cron") ? false : true;

$cronLog .= "Invoices are being created for " . count($companyUnitIDs) . " company units.</br>";
Operation::SaveCron($operationID, $cronLog, $type, $errorList);

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
            $productList = new ProductList("product");
            $productList->LoadProductListForAdmin($productGroupList->_items[$i]["group_id"]);
            for ($j = 0; $j < $productList->GetCountItems(); $j++) {
                $specificProduct = SpecificProductFactory::Create($productList->_items[$j]["code"]);
                if ($specificProduct == null) {
                    continue;
                }
                $checkLines = InvoiceLine::CheckLine(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodBefore,
                    $dateToPeriodBefore
                );

                $checkLinesRecurring = array_filter($checkLines, function($item) {
                    return $item["type"] == "recurring";
                });
                $checkLinesImplementation = array_filter($checkLines, function($item) {
                    return $item["type"] == "implementation";
                });

                $previousInvoice = new Invoice("billing");
                $previousInvoice->LoadByID($checkLinesRecurring[0]["invoice_id"]);

                $recurringLineData = $specificProduct->GetRecurringInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodBefore,
                    $dateToPeriodBefore,
                    $productGroupList->_items[$i]["group_id"],
                    null,
                    $previousInvoice->GetProperty("created")
                );
                $recurringInvoiceDetails = $recurringLineData["invoice_details"];
                $recurringLineData = $recurringLineData["line_data"];

                if ($recurringLineData !== null) {
                    foreach ($checkLinesRecurring as $checkLine) {
                        $recurringLineData['quantity'] -= $checkLine['quantity'];
                        $recurringLineData['cost'] -= $checkLine['cost'];
                        $recurringLineData['flex_cost'] -= $checkLine['flex_cost'];
                        $recurringLineData['flex_quantity'] -= $checkLine['flex_quantity'];
                        $recurringLineData['flex_unit_count'] -= $checkLine['flex_unit_count'];
                        $recurringLineData['flex_amount_sum'] -= $checkLine['flex_amount_sum'];
                        $recurringLineData['flex_unit_sum'] -= $checkLine['flex_unit_sum'];

                        $checkDetails = InvoiceDetails::CheckDetails(
                            $checkLine['invoice_id'],
                            ['recurring', 'recurring_flex']
                        );
                        if (count($checkDetails) == 0) {
                            $recurringInvoiceDetails = array();
                        }
                        foreach ($checkDetails as $check) {
                            foreach ($recurringInvoiceDetails as $key => $details) {
                                if (
                                    $check["employee_id"] != $details["employee_id"]
                                    || $check["product_id"] != $details["product_id"]
                                    || $check["type"] != $details["type"]
                                ) {
                                    continue;
                                }

                                $recurringInvoiceDetails[$key]["days_count"] -= $check["days_count"];
                                $recurringInvoiceDetails[$key]["cost"] -= $check["cost"];
                                $recurringInvoiceDetails[$key]["flex_cost"] -= $check["flex_cost"];
                                $recurringInvoiceDetails[$key]['flex_quantity'] -= $check['flex_quantity'];
                                $recurringInvoiceDetails[$key]['flex_employee_units'] -= $check['flex_employee_units'];
                                $recurringInvoiceDetails[$key]['flex_unit_count'] -= $check['flex_unit_count'];
                                $recurringInvoiceDetails[$key]['flex_free_units'] -= $check['flex_free_units'];
                                $recurringInvoiceDetails[$key]['flex_amount_sum'] -= $check['flex_amount_sum'];
                                $recurringInvoiceDetails[$key]['flex_unit_sum'] -= $check['flex_unit_sum'];
                            }
                        }
                    }
                    if (
                        round($recurringLineData['cost'], 2) > 0
                        || round($recurringLineData['flex_cost'], 2) > 0
                        || (count($checkLinesRecurring) > 0 && $recurringLineData['quantity'] > 0)
                    ) {
                        $lineDataListPeriodBefore[] = $recurringLineData;
                        $invoiceDetailsPeriodBefore[] = $recurringInvoiceDetails;
                    }
                }

                $recurringLineData = $specificProduct->GetRecurringInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodAfter,
                    $dateToPeriodAfter,
                    $productGroupList->_items[$i]["group_id"]
                );
                $recurringInvoiceDetails = $recurringLineData["invoice_details"];
                $recurringLineData = $recurringLineData["line_data"];
                if ($recurringLineData !== null) {
                    $lineDataListPeriodAfter[] = $recurringLineData;
                    $invoiceDetailsPeriodAfter[] = $recurringInvoiceDetails;
                }

                $implementationLineData = $specificProduct->GetImplementationInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodBefore,
                    $dateToPeriodBefore
                );
                $implementationInvoiceDetails = $implementationLineData["invoice_details"];
                $implementationLineData = $implementationLineData["line_data"];

                if ($implementationLineData !== null) {
                    foreach ($checkLinesImplementation as $checkLine) {
                        $implementationLineData['quantity'] -= $checkLine['quantity'];
                        $implementationLineData['cost'] -= $checkLine['cost'];

                        $checkDetails = InvoiceDetails::CheckDetails(
                            $checkLine['invoice_id'],
                            ['implementation']
                        );
                        if (count($checkDetails) == 0) {
                            $implementationInvoiceDetails = array();
                        }
                        foreach ($checkDetails as $check) {
                            foreach ($implementationInvoiceDetails as $key => $details) {
                                if (
                                    $check["employee_id"] != $details["employee_id"]
                                    || $check["product_id"] != $details["product_id"]
                                    || $check["type"] != $details["type"]
                                ) {
                                    continue;
                                }

                                $implementationInvoiceDetails[$key]["days_count"] -= $check["days_count"];
                                $implementationInvoiceDetails[$key]["cost"] -= $check["cost"];
                            }
                        }
                    }
                    if (
                        $implementationLineData['quantity'] > 0
                        || isset($implementationLineData['flex_quantity'])
                        && $implementationLineData['flex_quantity'] > 0
                    ) {
                        $lineDataListPeriodBefore[] = $implementationLineData;
                        $invoiceDetailsPeriodBefore[] = $implementationInvoiceDetails;
                    }
                }

                $implementationLineData = $specificProduct->GetImplementationInvoiceLineData(
                    $subUnitID,
                    $productList->_items[$j]["product_id"],
                    $dateFromPeriodAfter,
                    $dateToPeriodAfter
                );
                $implementationInvoiceDetails = $implementationLineData["invoice_details"];
                $implementationLineData = $implementationLineData["line_data"];
                if ($implementationLineData === null) {
                    continue;
                }

                $lineDataListPeriodAfter[] = $implementationLineData;
                $invoiceDetailsPeriodAfter[] = $implementationInvoiceDetails;
            }
        }

        $billableItemList = new BillableItemList("company");
        $billableItemList->loadBillableItemsForInvoice($subUnitID, $dateToPeriodAfter);
        $lineDataListPeriodAfter = array_merge($lineDataListPeriodAfter, $billableItemList->GetItems());

        $subUnitLines[$subUnitID] = array($lineDataListPeriodBefore, $lineDataListPeriodAfter);
        $subUnitDetails[$subUnitID] = array($invoiceDetailsPeriodBefore, $invoiceDetailsPeriodAfter);
        $periodBeforeLines += count($lineDataListPeriodBefore);
        $periodAfterLines += count($lineDataListPeriodAfter);
    }

    //create and send invoice if there are items to pay for
    if ($periodBeforeLines > 0) {
        $invoice = new Invoice($module);
        $invoice->LoadFromArray(array(
            "company_unit_id" => $companyUnitID,
            "date_from" => $dateFromPeriodBefore,
            "date_to" => $dateToPeriodBefore,
            "is_cron" => $isCron
        ));
        if ($invoice->Create()) {
            $countCreated++;
        } else {
            $errorList .= $invoice->GetErrorsAsString("</br>");
        }

        for ($i = 0; $i < count($subUnitIDs); $i++) {
            foreach ($subUnitLines[$subUnitIDs[$i]][0] as $lineData) {
                $invoiceLine = new InvoiceLine($module);
                $invoiceLine->LoadFromArray($lineData);
                $invoiceLine->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
                $invoiceLine->Create();
            }

            foreach ($subUnitDetails[$subUnitIDs[$i]][0] as $detailsList) {
                foreach ($detailsList as $detailsData) {
                    if ($detailsData['quantity'] <= 0 && $detailsData['flex_quantity'] <= 0) {
                        continue;
                    }

                    $invoiceDetails = new InvoiceDetails($module);
                    $invoiceDetails->LoadFromArray($detailsData);
                    $invoiceDetails->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
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
        "is_cron" => $isCron
    ));
    if ($invoice->Create()) {
        $countCreated++;
    } else {
        $errorList .= $invoice->GetErrorsAsString("</br>");
    }

    for ($i = 0; $i < count($subUnitIDs); $i++) {
        foreach ($subUnitLines[$subUnitIDs[$i]][1] as $lineData) {
            $invoiceLine = new InvoiceLine($module);
            $invoiceLine->LoadFromArray($lineData);
            $invoiceLine->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
            $invoiceLine->Create();
        }

        $billableItemList = new BillableItemList("company");
        $billableItemList->loadBillableItemsForInvoice($subUnitIDs[$i], $dateToPeriodAfter);
        $billableItemList->exportToInvoice($invoice->GetProperty("invoice_id"));

        foreach ($subUnitDetails[$subUnitIDs[$i]][1] as $detailsList) {
            foreach ($detailsList as $detailsData) {
                if (
                    $detailsData['quantity'] <= 0
                    && $detailsData['flex_quantity'] <= 0
                ) {
                    continue;
                }

                $invoiceDetails = new InvoiceDetails($module);
                $invoiceDetails->LoadFromArray($detailsData);
                $invoiceDetails->SetProperty("invoice_id", $invoice->GetProperty("invoice_id"));
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
