<?php

abstract class AbstractSpecificProduct
{
    protected $monthlyPriceOptionCode;
    protected $quarterlyPriceOptionCode;
    protected $yearlyPriceOptionCode;
    protected $monthlyDiscountOptionCode;
    protected $implementationPriceOptionCode;
    protected $implementationDiscountOptionCode;
    protected $frequencyOptionCode;

    /**
     * Returns invoice line data for recurring payment
     *
     * @param int $companyUnitID company_unit_id of company unit invoice is creating for
     * @param int $productID product_id of product line should be generated for
     * @param string $dateFrom start of invoice period
     * @param string $dateTo end of invoice period
     * @param int $productGroupID product_group_id for invoice preview
     * @param int $productGroupSortBy sort_order of product group for invoice preview
     *
     * @return array $result line data
     */
    public function GetRecurringInvoiceLineData(
        $companyUnitID,
        $productID,
        $dateFrom,
        $dateTo,
        $productGroupID = null,
        $productGroupSortBy = null,
        $invoiceDate = null
    ) {
        $result = null;
        $invoiceDetails = array();

        $employeeMap = ContractList::GetEmployeeInheritableContractDateList(
            $companyUnitID,
            $productID,
            $dateFrom,
            $dateTo,
            $invoiceDate
        );

        if (count($employeeMap) > 0) {
            $specificProductGroup = SpecificProductGroupFactory::Create($productGroupID);
            $productCode = Product::GetProductCodeByID($productID);

            $result = array(
                "product_id" => $productID,
                "product_code" => $productCode,
                "type" => INVOICE_LINE_TYPE_RECURRING,
                "quantity" => 0,
                "cost" => 0,
                "company_unit_id" => $companyUnitID,
                "product_group_code" => ProductGroup::GetProductGroupCodeByID($productGroupID),
                "sort_order" => $productGroupSortBy,
                "flex_quantity" => 0,
                "flex_unit_count" => 0,
                "flex_unit_price" => 0,
                "flex_unit_sum" => 0,
                "flex_amount_sum" => 0,
                "flex_unit_percentage" => 0,
                "flex_percentage_sum" => 0,
                "flex_cost" => 0
            );

            foreach ($employeeMap as $employeeID => $dateList) {
                $invoiceDetails[$employeeID]["employee_id"] = $employeeID;
                $invoiceDetails[$employeeID]["company_unit_id"] = $companyUnitID;
                $invoiceDetails[$employeeID]["product_id"] = $productID;
                $invoiceDetails[$employeeID]["date_list"] = $dateList;
                $invoiceDetails[$employeeID]["days_count"] = count($dateList);
                $invoiceDetails[$employeeID]["quantity"] = 0;
                $invoiceDetails[$employeeID]["cost"] = 0;
                $invoiceDetails[$employeeID]["flex_cost"] = 0;
                $invoiceDetails[$employeeID]["flex_quantity"] = 0;

                $flexOption = Product::IsFlexOptionOn($productCode, $employeeID, $dateTo);

                if (!$flexOption) {
                    $invoiceDetails[$employeeID]["type"] = "recurring";

                    $fullDiscount = false;
                    foreach ($dateList as $date) {
                        $cost = $this->GetRecurringPrice($companyUnitID, $date);
                        $invoiceDetails[$employeeID]["cost"] += $cost;

                        if (
                            Option::GetInheritableOptionValue(
                                OPTION_LEVEL_COMPANY_UNIT,
                                $this->monthlyDiscountOptionCode,
                                $companyUnitID,
                                $date
                            ) != 100
                        ) {
                            continue;
                        }

                        $fullDiscount = true;
                    }
                    $invoiceDetails[$employeeID]["cost"] = number_format($invoiceDetails[$employeeID]["cost"], 2);
                    if ($invoiceDetails[$employeeID]["cost"] > 0 || $fullDiscount) {
                        $result["quantity"]++;
                    }
                    $result["cost"] += $invoiceDetails[$employeeID]["cost"];
                    $invoiceDetails[$employeeID]["quantity"] = 1;
                } else {
                    $invoiceDetails[$employeeID]["type"] = "recurring_flex";

                    $result["flex_quantity"]++;
                    $invoiceDetails[$employeeID]["flex_quantity"] = 1;

                    $invoiceDetails[$employeeID]["flex_employee_units"] = $specificProductGroup->GetUnitCount(
                        $employeeID,
                        $dateFrom,
                        $dateTo
                    );
                    $invoiceDetails[$employeeID]["flex_free_units"] = $specificProductGroup->GetFlexOptionFreeUnits(
                        $employeeID,
                        $dateTo
                    );
                    $invoiceDetails[$employeeID]["flex_unit_count"] = $invoiceDetails[$employeeID]["flex_employee_units"] - $invoiceDetails[$employeeID]["flex_free_units"];
                    if ($invoiceDetails[$employeeID]["flex_unit_count"] < 0) {
                        $invoiceDetails[$employeeID]["flex_unit_count"] = 0;
                    }
                    $result["flex_unit_count"] += $invoiceDetails[$employeeID]["flex_unit_count"];
                    $invoiceDetails[$employeeID]["flex_unit_price"] = $result["flex_unit_price"] = $specificProductGroup->GetFlexOptionUnitPrice(
                        $employeeID,
                        $dateTo
                    );
                    $invoiceDetails[$employeeID]["flex_unit_sum"] = $invoiceDetails[$employeeID]["flex_unit_count"] * $invoiceDetails[$employeeID]["flex_unit_price"];
                    $result["flex_unit_sum"] += $invoiceDetails[$employeeID]["flex_unit_sum"];

                    if (!method_exists($specificProductGroup, "GetAvailableAmount")) {
                        $receipt = new Receipt("receipt", ["document_date" => $dateTo, "employee_id" => $employeeID]);
                        $invoiceDetails[$employeeID]["flex_unit"] = $productCode == PRODUCT__FOOD__MAIN || $productCode == PRODUCT__FOOD_VOUCHER__MAIN
                            ? $specificProductGroup->GetApiUnit($receipt)
                            : $specificProductGroup->GetUnit($receipt);
                        $invoiceDetails[$employeeID]["flex_amount_sum"] = $invoiceDetails[$employeeID]["flex_unit_count"] * $invoiceDetails[$employeeID]["flex_unit"];
                        $result["flex_amount_sum"] += $invoiceDetails[$employeeID]["flex_amount_sum"];
                    } else {
                        $availableAmount = $specificProductGroup->GetAvailableAmount(
                            $employeeID,
                            $dateFrom,
                            $dateTo,
                            null,
                            null,
                            true
                        );
                        $invoiceDetails[$employeeID]["flex_amount_sum"] = $availableAmount["amount"];
                        $result["flex_amount_sum"] += $invoiceDetails[$employeeID]["flex_amount_sum"];
                    }
                    $invoiceDetails[$employeeID]["flex_unit_percentage"] = $result["flex_unit_percentage"] = $specificProductGroup->GetFlexOptionUnitPercentage(
                        $employeeID,
                        $dateTo
                    );
                    $invoiceDetails[$employeeID]["flex_percentage_sum"] = $invoiceDetails[$employeeID]["flex_amount_sum"] * $invoiceDetails[$employeeID]["flex_unit_percentage"] / 100;
                    $result["flex_percentage_sum"] += $invoiceDetails[$employeeID]["flex_percentage_sum"];

                    $invoiceDetails[$employeeID]["flex_cost"] = $invoiceDetails[$employeeID]["flex_unit_sum"] + $invoiceDetails[$employeeID]["flex_percentage_sum"];
                    $result["flex_cost"] += $invoiceDetails[$employeeID]["flex_cost"];
                }
            }
        }

        return array("line_data" => $result, "invoice_details" => $invoiceDetails);
    }

    /**
     * Returns voucher invoice line data for recurring payment
     *
     * @param int $companyUnitID company_unit_id of company unit invoice is creating for
     * @param int $productID product_id of product line should be generated for
     * @param string $dateFrom start of invoice period
     * @param string $dateTo end of invoice period
     * @param int $productGroupID product_group_id for invoice preview
     * @param int $productGroupSortBy sort_order of product group for invoice preview
     * @param bool $forPreview
     * @param string $invoiceDate invoice date
     *
     * @return array $result line data
     */
    public function GetRecurringVoucherInvoiceLineData(
        $companyUnitID,
        $productID,
        $dateFrom = null,
        $dateTo,
        $productGroupID = null,
        $productGroupSortBy = null,
        $forPreview = false,
        $invoiceDate = null
    ) {
        $result = null;
        $invoiceDetails = array();

        $voucherList = new VoucherList("billing");
        $voucherList->LoadVoucherListByCompanyUnitID(
            $companyUnitID,
            $productGroupID,
            true,
            true,
            $invoiceDate
        );

        foreach ($voucherList->_items as $voucher) {
            if (
                (!$dateFrom || $voucher["voucher_date"] < $dateFrom || $voucher["voucher_date"] > $dateTo) &&
                ($dateFrom || $voucher["voucher_date"] > $dateTo)
            ) {
                continue;
            }

            $result[$voucher["amount"]] = array(
                "product_id" => $productID,
                "product_code" => Product::GetProductCodeByID($productID),
                "type" => INVOICE_LINE_TYPE_RECURRING,
                "quantity" => 0,
                "cost" => 0,
                "amount" => $voucher["amount"],
                "company_unit_id" => $companyUnitID,
                "product_group_code" => ProductGroup::GetProductGroupCodeByID($productGroupID),
                "sort_order" => $productGroupSortBy
            );
        }

        foreach ($voucherList->_items as $voucher) {
            if (
                (!$dateFrom || $voucher["voucher_date"] < $dateFrom || $voucher["voucher_date"] > $dateTo) &&
                ($dateFrom || $voucher["voucher_date"] > $dateTo)
            ) {
                continue;
            }

            $employeeID = $voucher["employee_id"];
            $invoiceDetails[$employeeID]["employee_id"] = $employeeID;
            $invoiceDetails[$employeeID]["company_unit_id"] = $companyUnitID;
            $invoiceDetails[$employeeID]["type"] = "recurring";
            $invoiceDetails[$employeeID]["product_id"] = $productID;
            $invoiceDetails[$employeeID]["voucher_list"][] = array(
                "voucher_id" => $voucher["voucher_id"],
                "amount" => $voucher["amount"]
            );

            $result[$voucher["amount"]]["quantity"]++;
            $result[$voucher["amount"]]["cost"] = $result[$voucher["amount"]]["amount"] * $result[$voucher["amount"]]["quantity"];
            if ($forPreview) {
                continue;
            }

            $result[$voucher["amount"]]["voucher_ids"][] = $voucher["voucher_id"];
        }

        return array("line_data" => $result, "invoice_details" => $invoiceDetails);
    }

    /**
     * Returns invoice line data for implementation payment
     *
     * @param int $companyUnitID company_unit_id of company unit invoice is creating for
     * @param int $productID product_id of product line should be generated for
     * @param string $dateFrom start of invoice period
     * @param string $dateTo end of invoice period
     * @param int $productGroupID product_group_id for invoice preview
     * @param int $productGroupSortBy sort_order of product group for invoice preview
     *
     * @return array $result line data
     */
    public function GetImplementationInvoiceLineData(
        $companyUnitID,
        $productID,
        $dateFrom,
        $dateTo,
        $productGroupID = null,
        $productGroupSortBy = null
    ) {
        $result = null;
        $invoiceDetails = array();
        if (Product::IsProductInheritable($productID)) {
            $employeeMap = ContractList::GetEmployeeInheritableContractCreatedCount(
                $companyUnitID,
                $productID,
                $dateFrom,
                $dateTo
            );
        } else {
            $employeeMap = ContractList::GetEmployeeContractCreatedCount(
                $companyUnitID,
                $productID,
                $dateFrom,
                $dateTo
            );
        }

        if (count($employeeMap) > 0) {
            $result = array(
                "product_id" => $productID,
                "product_code" => Product::GetProductCodeByID($productID),
                "type" => INVOICE_LINE_TYPE_IMPLEMENTATION,
                "quantity" => 0,
                "cost" => 0,
                "company_unit_id" => $companyUnitID,
                "product_group_code" => ProductGroup::GetProductGroupCodeByID($productGroupID),
                "sort_order" => $productGroupSortBy
            );
            foreach ($employeeMap as $employeeID => $dateList) {
                $invoiceDetails[$employeeID]["employee_id"] = $employeeID;
                $invoiceDetails[$employeeID]["company_unit_id"] = $companyUnitID;
                $invoiceDetails[$employeeID]["product_id"] = $productID;
                $invoiceDetails[$employeeID]["type"] = "implementation";
                $invoiceDetails[$employeeID]["date_list"] = $dateList;
                $invoiceDetails[$employeeID]["days_count"] = count($dateList);
                $invoiceDetails[$employeeID]["cost"] = 0;
                $fullDiscount = false;
                foreach ($dateList as $date) {
                    $cost = $this->GetImplementationPrice($companyUnitID, $date);
                    $invoiceDetails[$employeeID]["cost"] += $cost;

                    if (
                        Option::GetInheritableOptionValue(
                            OPTION_LEVEL_COMPANY_UNIT,
                            $this->implementationDiscountOptionCode,
                            $companyUnitID,
                            $date
                        ) != 100
                    ) {
                        continue;
                    }

                    $fullDiscount = true;
                }
                $invoiceDetails[$employeeID]["cost"] = number_format($invoiceDetails[$employeeID]["cost"], 2);
                if ($invoiceDetails[$employeeID]["cost"] > 0 || $fullDiscount) {
                    $result["quantity"]++;
                }
                $result["cost"] += $invoiceDetails[$employeeID]["cost"];
                $invoiceDetails[$employeeID]["quantity"] = 1;
            }
        }

        return array("line_data" => $result, "invoice_details" => $invoiceDetails);
    }

    /**
     * Returns recurring price for 1 day for selected date and employee
     *
     * @param int $employeeID
     * @param string $date
     *
     * @return float
     */
    private function GetRecurringPrice($companyUnitID, $date)
    {
        $monthlyPrice = $this->GetMonthlyPrice($companyUnitID, $date);
        $monthlyDiscount = Option::GetInheritableOptionValue(
            OPTION_LEVEL_COMPANY_UNIT,
            $this->monthlyDiscountOptionCode,
            $companyUnitID,
            $date
        );

        $daysInMonth = date("t", strtotime($date));
        $price = $monthlyPrice / $daysInMonth;
        if ($monthlyDiscount > 0) {
            $price = $price - ($price / 100 * $monthlyDiscount);
        }

        return $price;
    }

    /**
     * Returns implementation price for selected date and employee
     *
     * @param int $employeeID
     * @param string $date
     *
     * @return float
     */
    private function GetImplementationPrice($companyUnitID, $date)
    {
        $implementationPrice = Option::GetInheritableOptionValue(
            OPTION_LEVEL_COMPANY_UNIT,
            $this->implementationPriceOptionCode,
            $companyUnitID,
            $date
        );
        $implementationDiscount = Option::GetInheritableOptionValue(
            OPTION_LEVEL_COMPANY_UNIT,
            $this->implementationDiscountOptionCode,
            $companyUnitID,
            $date
        );

        $price = $implementationPrice;
        if ($implementationDiscount > 0) {
            $price = $price - ($price / 100 * $implementationDiscount);
        }

        return $price;
    }

    /**
     * Returns monthly price
     *
     * @param int $companyUnitID
     * @param string $date
     *
     * @return float
     */
    public function GetMonthlyPrice($companyUnitID, $date)
    {
        $monthlyPrice = null;
        if (isset($this->frequencyOptionCode)) {
            $frequency = Option::GetInheritableOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                $this->frequencyOptionCode,
                $companyUnitID,
                $date
            );
            if ($frequency == "quarterly" && isset($this->quarterlyPriceOptionCode)) {
                $quarterlyPrice = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->quarterlyPriceOptionCode,
                    $companyUnitID,
                    $date
                );
                $monthlyPrice = $quarterlyPrice;
            }
            if ($frequency == "yearly" && isset($this->yearlyPriceOptionCode)) {
                $yearlyPrice = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->yearlyPriceOptionCode,
                    $companyUnitID,
                    $date
                );
                $monthlyPrice = $yearlyPrice;
            }
        }

        if (!$monthlyPrice) {
            $monthlyPrice = Option::GetInheritableOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                $this->monthlyPriceOptionCode,
                $companyUnitID,
                $date
            );
        }

        return number_format($monthlyPrice, 2);
    }
}
