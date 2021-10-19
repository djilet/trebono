<?php

require_once __DIR__ . '/../../agreements/include/contract.php';

class ProductGroupList extends LocalObjectList
{
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ProductGroupList($module, $data = [])
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields([
            "sort_order_asc" => "g.sort_order ASC",
            "sort_order_desc" => "g.sort_order DESC",
        ]);
        $this->SetOrderBy("sort_order_asc");

        $this->params = [];
        $this->params["product_group"] = LoadImageConfig("product_group_image", $this->module, PRODUCT_GROUP_IMAGE);
    }

    /**
     * Loads full product group list for admin panel
     *
     * @param bool $filterByServicePermission If it is true then add check user permissions
     * @param bool $onlyWithReceipts If it is true then return only check-enabled services
     */
    public function LoadProductGroupListForAdmin(
        $filterByServicePermission = false,
        $onlyWithReceipts = false,
        $user = null
    ) {
        $where = [];
        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }

        if ($filterByServicePermission) {
            $permissionService = "service";
            $productGroupIDs = [];

            if ($user->Validate(["tax_auditor" => null])) {
                $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true);
                $where[] = "g.group_id NOT IN(" . implode(
                    ", ",
                    Connection::GetSQLArray(array_column($voucherProductGroupList, "group_id"))
                ) . ")";
            } else {
                if ($user->Validate([$permissionService => null])) {
                    $productGroupIDs = $user->GetPermissionLinkIDs($permissionService);
                }
            }

            if (count($productGroupIDs) > 0) {
                $where[] = "g.group_id IN(" . implode(", ", $productGroupIDs) . ")";
            }
        }

        if ($onlyWithReceipts) {
            $where[] = "g.receipts = 'Y'";
        }

        $hideFromAdmin = [
            PRODUCT_GROUP__BONUS,
            PRODUCT_GROUP__GIFT,
            PRODUCT_GROUP__BENEFIT,
            PRODUCT_GROUP__BONUS_VOUCHER,
        ];
        if (!$user->Validate(["root"])) {
            $where[] = "g.code NOT IN (" . implode(", ", Connection::GetSQLArray($hideFromAdmin)) . ")";
        }

        $query = "SELECT g.group_id, g.code, g.created, g.title, g.receipts, g.sort_order, 
						g.product_group_image, g.product_group_image_config, g.voucher 
					FROM product_group AS g"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Loads product group list for api.
     * Perhaps there will be loaded only product groups enabled for current employee for current date.
     *
     * @param Employee $employee
     * @param $date
     * @param $yearlyStatistics boolean - if true, return all product groups that were active during the year
     * @param $isAPI boolean
     * @param $user User
     */
    public function LoadProductGroupListForApi(
        $employee,
        $date = false,
        $yearlyStatistics = false,
        $isAPI = false,
        $user = null
    ) {
        $currentDate = $date ?: GetCurrentDate();
        $dateFrom = date("Y-m-01", strtotime($currentDate . " -1 month"));
        $dateTo = date("Y-m-t", strtotime($currentDate));

        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }
        $hideFromAdmin = [
            PRODUCT_GROUP__BONUS,
            PRODUCT_GROUP__GIFT,
            PRODUCT_GROUP__BENEFIT,
            PRODUCT_GROUP__BONUS_VOUCHER,
        ];
        $query = "SELECT g.group_id, g.code, g.created, g.title, g.multiple_receipt_file, 
					g.product_group_image, g.product_group_image_config, g.voucher, 
					SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee 
				FROM product_group AS g
					LEFT JOIN receipt AS r ON r.group_id=g.group_id 
                        AND r.employee_id=" . $employee->GetIntProperty("employee_id") . " 
                        AND DATE(r.created) >= " . Connection::GetSQLDate($dateFrom) . "
                        AND DATE(r.created) <= " . Connection::GetSQLDate($dateTo) . "
					LEFT JOIN receipt_comment AS c ON c.receipt_id=r.receipt_id 
				WHERE g.receipts!='N'
				    " . (!$user->Validate(["root"]) ? "
				    AND g.code NOT IN (" . implode(", ", Connection::GetSQLArray($hideFromAdmin)) . ") 
                    " : "") . "
				GROUP BY g.group_id";
        $this->LoadFromSQL($query);

        $contract = new Contract("contract");
        $agreementsContract = new AgreementsContract("agreements");

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($employee->GetIntProperty("company_unit_id"));

        $receiptTypeList = new ReceiptTypeList($this->module);

        $existBaseContract = false;
        $existActiveBaseContract = false;
        $existActiveInterruptionContract = false;
        $existNextPayrollForLastBaseContract = false;
        $deactivationReason = null;

        /* check base contract */
        $lastBaseContract = new Contract("contract");
        if (
            $lastBaseContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $employee->GetIntProperty('employee_id'),
                Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                false,
                true
            )
        ) {
            $existBaseContract = true;
            $endDateOfLastBaseContract = $lastBaseContract->GetProperty("end_date");

            if ($endDateOfLastBaseContract == null || strtotime($endDateOfLastBaseContract) > strtotime($currentDate)) {
                $existActiveBaseContract = true;

                /* check interruption contract */
                $existActiveInterruptionContract = $contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION),
                    $employee->GetIntProperty('employee_id'),
                    $currentDate
                );
            } else {
                $monthNextPayroll = date('m', strtotime($endDateOfLastBaseContract)) + 1;
                $monthNextPayroll = $monthNextPayroll < 10 ? "0" . $monthNextPayroll : $monthNextPayroll;
                $monthAndYearNextPayroll = $monthNextPayroll <= 12
                    ? date("Y", strtotime($endDateOfLastBaseContract)) . $monthNextPayroll
                    : date("Y", strtotime($endDateOfLastBaseContract . " + 1 year")) . "01";

                $stmt = GetStatement();
                $query = "SELECT payroll_id FROM payroll 
                            WHERE company_unit_id=" . intval($companyUnit->GetProperty("company_unit_id")) . " AND 
                            payroll_month=" . Connection::GetSQLString($monthAndYearNextPayroll);

                if (boolval($stmt->FetchRow($query))) {
                    $existNextPayrollForLastBaseContract = true;
                }

                $deactivationReason = Option::GetCurrentValue(
                    OPTION_LEVEL_EMPLOYEE,
                    Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
                    $employee->GetIntProperty('employee_id')
                );
            }
        }

        /* check products contracts */
        foreach ($this->_items as &$item) {
            $receiptTypeList->LoadReceiptTypeListForProductGroup($item["group_id"]);
            $item["receipt_type_list"] = $receiptTypeList->GetItems();

            $specificProductGroup = SpecificProductGroupFactory::Create($item["group_id"]);
            if ($specificProductGroup == null) {
                continue;
            }
            $productCode = $specificProductGroup->GetMainProductCode();
            $productID = Product::GetProductIDByCode($productCode);

            if ($existBaseContract) {
                //if it's voucher service, use same calculations as for API
                if ($isAPI || $item["voucher"] == "Y") {
                    $lastProductContract = new Contract('contract');
                    $existProductContract = false;
                    $existActiveProductContract = false;
                    $existNextPayrollForLastProductContract = false;
                    $isVoucherService = false;
                    $isFoodVoucherService = false;

                    if (
                        $lastProductContract->LoadLatestActiveContract(
                            OPTION_LEVEL_EMPLOYEE,
                            $employee->GetIntProperty("employee_id"),
                            $productID,
                            false,
                            true
                        )
                    ) {
                        $existProductContract = true;
                        $endDateOfLastProductContract = $lastProductContract->GetProperty("end_date");

                        if (
                            $endDateOfLastProductContract == null
                            || strtotime($endDateOfLastProductContract) > strtotime($currentDate)
                        ) {
                            $existActiveProductContract = true;
                        } else {
                            $monthNextPayroll = date('m', strtotime($endDateOfLastProductContract)) + 1;
                            $monthNextPayroll = $monthNextPayroll < 10 ? "0" . $monthNextPayroll : $monthNextPayroll;
                            if ($monthNextPayroll <= 12) {
                                $monthAndYearNextPayroll = date(
                                    "Y",
                                    strtotime($endDateOfLastProductContract)
                                ) . $monthNextPayroll;
                            } else {
                                $monthAndYearNextPayroll = date(
                                    "Y",
                                    strtotime($endDateOfLastProductContract . " + 1 year")
                                ) . "01";
                            }

                            $stmt = GetStatement();
                            $query = "SELECT payroll_id FROM payroll 
                            WHERE company_unit_id=" . intval($companyUnit->GetProperty("company_unit_id")) . " AND 
                            payroll_month=" . Connection::GetSQLString($monthAndYearNextPayroll);

                            if (boolval($stmt->FetchRow($query))) {
                                $existNextPayrollForLastProductContract = true;
                            }
                        }
                    }

                    if ($existProductContract) {
                        /* additional check for voucher services */
                        if ($item["voucher"] == "Y") {
                            $isFoodVoucherService = $productCode == PRODUCT__FOOD_VOUCHER__MAIN;
                            if (!$isFoodVoucherService) {
                                $isVoucherService = true;
                            }

                            $existOpenVoucher = false;
                            $voucherList = $specificProductGroup->GetReceiptMappedVoucherList(
                                $employee->GetProperty('employee_id')
                            );
                            foreach ($voucherList as $voucher) {
                                if (
                                    strtotime($voucher["end_date"]) >= strtotime($currentDate) &&
                                    $voucher["amount_left"] > 0
                                ) {
                                    $existOpenVoucher = true;
                                    break;
                                }
                            }
                        }

                        /* access for food voucher service */
                        if ($isFoodVoucherService) {
                            if (
                                (!$existActiveBaseContract && $deactivationReason === 'end' && $existNextPayrollForLastBaseContract) ||
                                (!$existActiveProductContract && $deactivationReason === 'end' && $existNextPayrollForLastProductContract) ||
                                (!$existActiveBaseContract && $deactivationReason !== 'end' && $existNextPayrollForLastBaseContract && !$existOpenVoucher) ||
                                (!$existActiveProductContract && $deactivationReason !== 'end' && $existNextPayrollForLastProductContract && !$existOpenVoucher) ||
                                ($existActiveBaseContract && $existActiveInterruptionContract)
                            ) {
                                $item["active"] = false;
                            } else {
                                $item["active"] = true;
                            }
                        /* access for another voucher services */
                        } elseif ($isVoucherService) {
                            if (
                                (!$existActiveBaseContract && $existNextPayrollForLastBaseContract && !$existOpenVoucher) ||
                                (!$existActiveProductContract && $existNextPayrollForLastProductContract && !$existOpenVoucher) ||
                                ($existActiveBaseContract && $existActiveInterruptionContract && !$existOpenVoucher)
                            ) {
                                $item["active"] = false;
                            } else {
                                $item["active"] = true;
                            }
                        /* access for another services */
                        } else {
                            $productGroupsExceptions = [
                                PRODUCT__AD__MAIN,
                                PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN,
                            ];

                            if (
                                (!$existActiveBaseContract && $existNextPayrollForLastBaseContract) ||
                                (!$existActiveProductContract && $existNextPayrollForLastProductContract) ||
                                ($existActiveBaseContract && $existActiveInterruptionContract && !in_array($productCode,
                                        $productGroupsExceptions))
                            ) {
                                $item["active"] = false;
                            } else {
                                $item["active"] = true;
                            }
                        }
                    } else {
                        $item["active"] = false;
                    }
                }
                if (!$isAPI) {
                    $existCurrentContract = false;
                    $contract = new Contract('contract');
                    if (
                        $contract->ContractExist(
                            OPTION_LEVEL_EMPLOYEE,
                            $productID,
                            $employee->GetIntProperty('employee_id'),
                            $currentDate
                        )
                    ) {
                        $existCurrentContract = true;
                    }

                    if ($item["voucher"] == "Y") {
                        if ($yearlyStatistics && !$existCurrentContract) {
                            $item['hide'] = true;
                            if (!$item["active"]) {
                                $item["hide_monthly"] = true;
                            }
                        }
                    } else {
                        $item['active'] = $existCurrentContract;
                        if (
                            $yearlyStatistics
                            && !$existCurrentContract
                            && $contract->ContractExist(
                                OPTION_LEVEL_EMPLOYEE,
                                $productID,
                                $employee->GetIntProperty('employee_id'),
                                date("Y-01-01", strtotime($currentDate)),
                                $currentDate
                            )
                        ) {
                            $item['active'] = true;
                            $item['hide'] = true;
                            $item["hide_monthly"] = true;
                        }
                    }
                }
            } else {
                $item["active"] = false;
            }

            if ($item["code"] == PRODUCT_GROUP__RECREATION) {
                $item["max_doc_receipt_file_count"] = Option::GetOptionValue(
                    OPTION_LEVEL_GLOBAL,
                    OPTION__RECREATION__MAX_DOC_RECEIPT_FILE_COUNT,
                    0,
                    $currentDate
                );
            } else {
                $item["max_doc_receipt_file_count"] = 0;
            }

            $setsOfGoodsServices = [PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__BONUS_VOUCHER];
            if (in_array($item["code"], $setsOfGoodsServices)) {
                $item["voucher_preference"] = true;

                $callback = static function ($str) {
                    $str = trim($str);

                    return $str;
                };

                $setsOfGoods = Config::GetConfigValue("voucher_sets_of_goods");
                $setsOfGoods = preg_split("/\r\n|\r|\n/", $setsOfGoods);
                $setsOfGoods = array_map($callback, $setsOfGoods);

                $result = [];
                foreach ($setsOfGoods as $key => $set) {
                    if ($key == 0) {
                        continue;
                    }
                    $result[] = [
                        "set_of_goods" => $set,
                    ];
                }
                $item["receipt_sets_of_goods"] = $result;

            } elseif ($item["voucher"] == "Y") {
                $item["check_available_vouchers"] = true;
            }

            $item["number_necessary_actions_employee"] = ReceiptList::GetNumberNecessaryActionsByEmployee(
                $item["group_id"],
                $employee->GetIntProperty("employee_id")
            );

            if ($companyUnit->GetProperty("agreement_enable") == "Y") {
                $item["is_agreement_must_be_accepted"] = $agreementsContract->IsAgreementMustBeAccepted(
                    $item['group_id'],
                    $employee->GetIntProperty("company_unit_id"),
                    $employee->GetIntProperty("employee_id")
                );
            } else {
                $item["is_agreement_must_be_accepted"] = false;
            }
        }

        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["title_translation"] = GetTranslation(
                "product-group-" . $this->_items[$i]["code"],
                $this->module
            );

            foreach ($this->params as $k => $v) {
                PrepareImagePath($this->_items[$i], $k, $v, CONTAINER__PRODUCT, "product_group/");
            }
        }
    }

    /**
     * Gets full product group list
     *
     * @param bool $needCheckImage - images need OCR check
     * @param bool $isVoucher - services have vouchers
     * @param bool $receipts - services have receipts
     * @param bool $newGenerationVoucher - voucher services with new rules
     * @param User $user - current user
     *
     * @return array|bool|null
     */
    public static function GetProductGroupList(
        $needCheckImage = false,
        $isVoucher = false,
        $receipts = false,
        $newGenerationVoucher = false,
        $user = null
    ) {
        $stmt = GetStatement(DB_MAIN);
        $newGenerationVoucherList = [
            PRODUCT_GROUP__BENEFIT_VOUCHER,
            PRODUCT_GROUP__FOOD_VOUCHER,
            PRODUCT_GROUP__GIFT_VOUCHER,
            PRODUCT_GROUP__BONUS_VOUCHER,
        ];

        $where = [];
        if ($needCheckImage == "Y") {
            $where[] = "g.need_check_image='Y'";
        } elseif ($needCheckImage == "N") {
            $where[] = "g.need_check_image='N'";
        }

        if ($isVoucher == "Y") {
            $where[] = "g.voucher='Y'";
        } elseif ($isVoucher == "N") {
            $where[] = "g.voucher='N'";
        }

        if ($receipts == "Y") {
            $where[] = "g.receipts='Y'";
        } elseif ($receipts == "N") {
            $where[] = "g.receipts='N'";
        }

        if ($newGenerationVoucher) {
            $where[] = "g.code IN (" . implode(", ", Connection::GetSQLArray($newGenerationVoucherList)) . ")";
        }

        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }
        $hideFromAdmin = [
            PRODUCT_GROUP__BONUS,
            PRODUCT_GROUP__GIFT,
            PRODUCT_GROUP__BENEFIT,
            PRODUCT_GROUP__BONUS_VOUCHER,
        ];
        if (!$user->Validate(["root"])) {
            $where[] = "g.code NOT IN (" . implode(", ", Connection::GetSQLArray($hideFromAdmin)) . ")";
        }

        $query = "SELECT g.group_id, g.code, g.created, g.title, g.receipts, g.sort_order,
					g.product_group_image, g.product_group_image_config, g.voucher
					FROM product_group AS g"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "")
            . " ORDER BY g.sort_order";

        return $stmt->FetchList($query);
    }
}
