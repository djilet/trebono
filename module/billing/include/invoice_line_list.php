<?php

class InvoiceLineList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function InvoiceLineList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "type_asc" => "l.company_unit_id, l.billable_item_id ASC, l.type ASC, g.sort_order ASC, l.invoice_line_id ASC",
            "type_desc" => "l.company_unit_id, l.billable_item_id ASC, l.type DESC, g.sort_order DESC, l.invoice_line_id DESC"
        ));
        $this->SetOrderBy("type_asc");
    }

    /**
     * Loads invoice line list for invoice view
     *
     * @param int $invoiceID invoice_id of invoice lines should be loaded for
     */
    public function LoadInvoiceLineList($invoiceID)
    {
        $where = array();
        $where[] = "l.invoice_id=" . intval($invoiceID);

        $query = "SELECT l.*,
						p.code AS product_code, 
						g.code AS product_group_code,
						b.price, b.date_start, b.date_end, b.item_name, b.discount
					FROM invoice_line AS l 
						LEFT JOIN product AS p ON p.product_id=l.product_id 
						LEFT JOIN product_group AS g ON g.group_id=p.group_id 
						LEFT JOIN billable_item AS b ON b.item_id=l.billable_item_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     *
     * @param string $dateFrom date_from for invoice preview
     * @param string $dateTo date_to for invoice preview
     */
    private function PrepareContentBeforeShow($dateFrom = null, $dateTo = null)
    {
        $flexItemList = [];
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["product_title_translation"] = GetTranslation(
                "product-" . $this->_items[$i]["product_code"],
                "billing"
            );
            $this->_items[$i]["product_group_title_translation"] = GetTranslation(
                "product-group-" . $this->_items[$i]["product_group_code"],
                "billing"
            );
            $companyUnitID = $this->_items[$i]['company_unit_id'];
            if (isset($this->_items[$i]["invoice_id"]) && intval($this->_items[$i]["invoice_id"] > 0)) {
                $invoice = new Invoice($this->module);
                $invoice->LoadByID($this->_items[$i]["invoice_id"]);
                $dateFrom = $invoice->GetProperty("date_from");
                $dateTo = $invoice->GetProperty("date_to");
            }
            if ($this->_items[$i]["type"] == "recurring") {
                $specificProduct = SpecificProductFactory::Create($this->_items[$i]["product_code"]);
                $this->_items[$i]["user_per_month"] = $specificProduct->GetMonthlyPrice($companyUnitID, $dateTo);
                $this->_items[$i]["discount"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->_items[$i]["product_code"] . "__monthly_discount",
                    $companyUnitID,
                    $dateTo
                );
            } elseif ($this->_items[$i]["type"] == "implementation") {
                $this->_items[$i]["user_per_month"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->_items[$i]["product_code"] . "__implementation_price",
                    $companyUnitID,
                    $dateFrom
                );
                $this->_items[$i]["discount"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->_items[$i]["product_code"] . "__implementation_discount",
                    $companyUnitID,
                    $dateFrom
                );
                if (Product::IsProductInheritable($this->_items[$i]["product_id"])) {
                    $date = ContractList::GetInheritableDateContractCreated(
                        $companyUnitID,
                        $this->_items[$i]["product_id"],
                        $dateFrom,
                        $dateTo
                    );
                } else {
                    $date = ContractList::GetDateContractCreated(
                        $companyUnitID,
                        $this->_items[$i]["product_id"],
                        $dateFrom,
                        $dateTo
                    );
                }

                $monthFrom = date("n", strtotime($date["date_from"]));
                $monthTo = date("n", strtotime($date["date_to"]));

                if ($monthFrom == $monthTo) {
                    $this->_items[$i]["date_from"] = $date["date_from"];
                } else {
                    $this->_items[$i]["date_from"] = $date["date_from"];
                    $this->_items[$i]["date_to"] = $date["date_to"];
                }
            } elseif ($this->_items[$i]["type"] == "bill") {
                $this->_items[$i]["product_title_translation"] = $this->_items[$i]["item_name"];
                $this->_items[$i]["user_per_month"] = $this->_items[$i]["price"];
            }

            if ($this->_items[$i]["product_group_code"] == PRODUCT_GROUP__FOOD && $this->_items[$i]["product_code"] != PRODUCT__FOOD__MAIN) {
                $this->_items[$i]["secondary_product"] = 1;
            }

            if ($i == 0 || $this->_items[$i]["type"] != $this->_items[$i - 1]["type"]) {
                $this->_items[$i]["show_type"] = 1;
            }
            if ($i == 0 || $this->_items[$i]["product_group_code"] != $this->_items[$i - 1]["product_group_code"]) {
                $this->_items[$i]["show_product_group"] = 1;
            }

            if (isset($this->_items[$i]["flex_quantity"]) && $this->_items[$i]["flex_quantity"] > 0) {
                $flexItemList[$i] = $this->_items[$i];
                $flexItemList[$i]["product_title_translation"] = GetTranslation(
                    "product_flex-" . $this->_items[$i]["product_code"],
                    "billing"
                );
                $flexItemList[$i]["product_translation_unit_count_flex"] = GetTranslation(
                    "product_flex_unit_count-" . $this->_items[$i]["product_code"],
                    "billing"
                );
                $flexItemList[$i]["product_translation_total_amount_flex"] = GetTranslation(
                    "product_flex_total_amount-" . $this->_items[$i]["product_code"],
                    "billing"
                );
                $flexItemList[$i]["quantity"] = 0;
                $flexItemList[$i]["cost"] = $flexItemList[$i]["flex_unit_sum"] + $flexItemList[$i]["flex_percentage_sum"];
                $this->_items[$i]["flex_quantity"] = 0;
            }

            if (
                !isset($this->_items[$i + 1]) ||
                $this->_items[$i]["company_unit_id"] != $this->_items[$i + 1]["company_unit_id"]
            ) {
                foreach ($flexItemList as $j => $flexItem) {
                    $this->InsertItem($flexItem, $j);
                }
                $flexItemList = [];
            }
        }
    }

    /**
     * Simulates creation of invoice line list for invoice preview
     *
     * @param Invoice $invoice invoice data
     */
    public function LoadInvoiceLineListForPreview(Invoice $invoice)
    {
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($invoice->GetIntProperty("company_unit_id"));

        $dateFrom = $invoice->GetProperty("date_from");
        $dateTo = $invoice->GetProperty("date_to");
        $after = $invoice->GetProperty("for_period_after");
        $invoiceType = $invoice->GetProperty("invoice_type");

        $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        $voucherProductGroupList = array_column($voucherProductGroupList, "code");
        $voucherProductList = ProductList::GetVoucherProductList(true);
        $voucherProductList = array_column($voucherProductList, "code");

        $subUnitIDs = CompanyUnitList::GetAllCompanyUnitIDs($companyUnit->GetProperty("company_id"));

        $subUnitLines = array();
        foreach ($subUnitIDs as $subUnitID) {
            $subUnit = new CompanyUnit("company");
            $subUnit->LoadByID($subUnitID);

            //iterate the products and collect their recurring and implementation invoice data
            $lineDataList = array();

            $productGroupList = new ProductGroupList("product");
            $productGroupList->LoadProductGroupListForAdmin();
            if ($invoiceType == "voucher_invoice") {
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
                        if ($specificProduct == null) {
                            continue;
                        }

                        $recurringLineData = $specificProduct->GetRecurringVoucherInvoiceLineData(
                            $subUnitID,
                            $productList->_items[$j]["product_id"],
                            $dateFrom,
                            $dateTo,
                            $productGroupList->_items[$i]["group_id"],
                            $productGroupList->_items[$i]["sort_order"],
                            true
                        );
                        $recurringLineData = $recurringLineData["line_data"];

                        if ($recurringLineData === null) {
                            continue;
                        }

                        $lineDataList[] = $recurringLineData;
                    }
                }
            } else {
                for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
                    $productList = new ProductList("product");
                    $productList->LoadProductListForAdmin($productGroupList->_items[$i]["group_id"]);
                    for ($j = 0; $j < $productList->GetCountItems(); $j++) {
                        $specificProduct = SpecificProductFactory::Create($productList->_items[$j]["code"]);
                        if ($specificProduct == null) {
                            continue;
                        }

                        $previousInvoiceDate = $dateFrom;
                        if (!$after) {
                            $checkLines = InvoiceLine::CheckLine(
                                $subUnitID,
                                $productList->_items[$j]["product_id"],
                                $dateFrom,
                                $dateTo
                            );
                            $checkLinesRecurring = array_filter($checkLines, function($item) {
                                return $item["type"] == "recurring";
                            });
                            $checkLinesImplementation = array_filter($checkLines, function($item) {
                                return $item["type"] == "implementation";
                            });

                            $previousInvoice = new Invoice("billing");
                            $previousInvoice->LoadByID($checkLinesRecurring[0]["invoice_id"]);
                            $previousInvoiceDate = $previousInvoice->GetProperty("created");
                        }
                        if ($after) {
                            $recurringLineData = $specificProduct->GetRecurringInvoiceLineData(
                                $subUnitID,
                                $productList->_items[$j]["product_id"],
                                $dateFrom,
                                $dateTo,
                                $productGroupList->_items[$i]["group_id"],
                                $productGroupList->_items[$i]["sort_order"]
                            );
                        } else {
                            $recurringLineData = $specificProduct->GetRecurringInvoiceLineData(
                                $subUnitID,
                                $productList->_items[$j]["product_id"],
                                $dateFrom,
                                $dateTo,
                                $productGroupList->_items[$i]["group_id"],
                                $productGroupList->_items[$i]["sort_order"],
                                $previousInvoiceDate
                            );
                        }
                        $recurringLineData = $recurringLineData["line_data"];
                        if (!$after && $recurringLineData !== null) {
                            foreach ($checkLinesRecurring as $checkLine) {
                                $recurringLineData['quantity'] -= $checkLine['quantity'];
                                $recurringLineData['cost'] -= $checkLine['cost'];
                                $recurringLineData['flex_cost'] -= $checkLine['flex_cost'];
                                $recurringLineData['flex_quantity'] -= $checkLine['flex_quantity'];
                                $recurringLineData['flex_unit_count'] -= $checkLine['flex_unit_count'];
                                $recurringLineData['flex_amount_sum'] -= $checkLine['flex_amount_sum'];
                                $recurringLineData['flex_unit_sum'] -= $checkLine['flex_unit_sum'];
                            }
                            if (
                                round($recurringLineData['cost'], 2) > 0
                                || round($recurringLineData['flex_cost'], 2) > 0
                            ) {
                                $lineDataList[] = $recurringLineData;
                            }
                        } elseif ($after && $recurringLineData !== null) {
                            $lineDataList[] = $recurringLineData;
                        }

                        $implementationLineData = $specificProduct->GetImplementationInvoiceLineData(
                            $subUnitID,
                            $productList->_items[$j]["product_id"],
                            $dateFrom,
                            $dateTo,
                            $productGroupList->_items[$i]["group_id"],
                            $productGroupList->_items[$i]["sort_order"]
                        );
                        $implementationLineData = $implementationLineData["line_data"];
                        if (!$after && $implementationLineData !== null) {
                            foreach ($checkLinesImplementation as $checkLine) {
                                $implementationLineData['quantity'] -= $checkLine['quantity'];
                                $implementationLineData['cost'] -= $checkLine['cost'];
                            }
                            if ($implementationLineData['quantity'] > 0) {
                                $lineDataList[] = $implementationLineData;
                            }
                        } elseif ($after && $implementationLineData !== null) {
                            $lineDataList[] = $implementationLineData;
                        }
                    }
                }

                foreach ($lineDataList as $key => $line) {
                    $lineDataList[$key]["billable_item_id"] = 0;
                }

                if ($after) {
                    $billableItemList = new BillableItemList("company");
                    $billableItemList->loadBillableItemsForInvoice($subUnitID, $dateTo, true);
                    $lineDataList = array_merge($lineDataList, $billableItemList->GetItems());
                }
            }
            $subUnitLines[$subUnitID] = $lineDataList;
        }
        foreach ($subUnitLines as $lineDataProductGroup) {
            if ($invoiceType == "voucher_invoice") {
                foreach ($lineDataProductGroup as $lineData) {
                    $this->AppendFromArray($lineData);
                }
            } else {
                $this->AppendFromArray($lineDataProductGroup);
            }
        }

        array_multisort(
            array_column($this->_items, "company_unit_id"),
            SORT_ASC,
            array_column($this->_items, "billable_item_id"),
            SORT_ASC,
            array_column($this->_items, "type"),
            SORT_ASC,
            array_column($this->_items, "sort_order"),
            SORT_ASC,
            $this->_items
        );
        $this->PrepareContentBeforeShow($dateFrom, $dateTo);
    }
}
