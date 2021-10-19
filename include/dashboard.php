<?php

class Dashboard extends LocalObject
{
    /**
     * Loads dashboard statistics
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *          <li><u>DateFrom</u> - string - property for start "date" field filtration</li>
     *        <li><u>DateTo</u> - string - property for end "date" field filtration</li>
     *        </ul>
     */
    public function LoadDashboard($request)
    {
        $this->SetProperty(
            "InvoiceImplementationStatistics",
            InvoiceList::GetInvoiceListStatisticsForDashboard(
                $request->GetProperty("DateFrom"),
                $request->GetProperty("DateTo"),
                $request->GetProperty("FilterInvoices"),
                "implementation",
                "invoice"
            )
        );
        $this->SetProperty(
            "InvoiceRecurringStatistics",
            InvoiceList::GetInvoiceListStatisticsForDashboard(
                $request->GetProperty("DateFrom"),
                $request->GetProperty("DateTo"),
                $request->GetProperty("FilterInvoices"),
                "recurring",
                "invoice"
            )
        );
        $this->SetProperty(
            "VoucherInvoiceRecurringStatistics",
            InvoiceList::GetInvoiceListStatisticsForDashboard(
                $request->GetProperty("DateFrom"),
                $request->GetProperty("DateTo"),
                $request->GetProperty("FilterInvoices"),
                "recurring",
                "voucher_invoice"
            )
        );
        $this->SetProperty(
            "StatusConversion",
            ReceiptList::GetReceiptStatusStatisticsForDashboard(
                $request->GetProperty("DateFrom"),
                $request->GetProperty("DateTo")
            )
        );
        $receiptList = new ReceiptList("receipt");
        $receiptList->SetOrderBy("denial_reason_employee_id_asc");
        $receiptList->LoadReceiptListForDashboard(new LocalObject([
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
        ]));
        $this->SetProperty("ReceiptList", $receiptList->GetItems());
        $receiptCount = $receiptList->GetCountItems();
        $this->SetProperty("ReceiptCount", $receiptCount);
        $receiptList->LoadReceiptListForDashboard(new LocalObject([
            "FilterStatus" => ["denied"],
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
        ]));

        $this->SetProperty("ReceiptDeniedList", $receiptList->GetItems());
        $this->SetProperty("ReceiptDeniedCount", $receiptList->GetCountItems());
        $this->SetProperty(
            "ReceiptDeniedPercent",
            round(($receiptCount > 0 ? $receiptList->GetCountItems() / $receiptCount * 100 : 0), 2)
        );

        $this->SetProperty("DenialReasonSelectList", Receipt::GetDenialReasonList());

        $employeeList = EmployeeList::GetArchivePropertyHistory(new LocalObject([
            "FilterArchive" => "N",
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
            "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
        ]));
        $this->SetProperty("EmployeeAddedList", $employeeList);
        $this->SetProperty("EmployeeAddedCount", count($employeeList));
        $employeeList = EmployeeList::GetActiveEmployeeIDs(
            false,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            false,
            false,
            true
        );
        $this->SetProperty("EmployeeTotalCount", count($employeeList));

        $employeeList = EmployeeList::GetArchivePropertyHistory(new LocalObject([
            "FilterArchive" => "Y",
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
            "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
        ]));
        $this->SetProperty("EmployeeDeactivatedList", $employeeList);
        $this->SetProperty("EmployeeDeactivatedCount", count($employeeList));
        $employeeList = EmployeeList::GetActiveEmployeeIDs(
            true,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            false,
            false,
            true
        );
        $this->SetProperty("EmployeeArchiveTotalCount", count($employeeList));

        $companyUnitList = CompanyUnitList::GetArchivePropertyHistory(new LocalObject([
            "FilterArchive" => "N",
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
            "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
        ]));
        $this->SetProperty("CompanyUnitAddedList", $companyUnitList);
        $this->SetProperty("CompanyUnitAddedCount", count($companyUnitList));
        $companyUnitList = CompanyUnitList::GetActiveCompanyUnitIDs(
            false,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
        );
        $this->SetProperty("CompanyUnitTotalCount", count($companyUnitList));

        $companyUnitList = CompanyUnitList::GetArchivePropertyHistory(new LocalObject([
            "FilterArchive" => "Y",
            "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
            "FilterCreatedTo" => $request->GetProperty("DateTo"),
            "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
        ]));
        $this->SetProperty("CompanyUnitDeactivatedList", $companyUnitList);
        $this->SetProperty("CompanyUnitDeactivatedCount", count($companyUnitList));
        $companyUnitList = CompanyUnitList::GetActiveCompanyUnitIDs(
            true,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
        );
        $this->SetProperty("CompanyUnitArchiveTotalCount", count($companyUnitList));
        $distinctEmployeeList = [];
        foreach ($receiptList->GetItems() as $item) {
            if (in_array($item["employee_id"], array_column($distinctEmployeeList, "employee_id"))) {
                continue;
            }

            $distinctEmployeeList[] = $item;
        }

        $this->SetProperty("EmployeeSelectList", $distinctEmployeeList);
    }

    /**
     * Loads technical dashboard statistics
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *          <li><u>DateFrom</u> - string - property for start "date" field filtration</li>
     *        <li><u>DateTo</u> - string - property for end "date" field filtration</li>
     *        </ul>
     */
    public function LoadTechnicalDashboard($request)
    {

        if ($request->GetProperty("DateRange")) {
            [$dateFrom, $dateTo] = explode(" - ", $request->GetProperty("DateRange"));
        }

        $queueList = array();
        /*$queueTitles = array("send_mail", "error_handler", "line_recognize", "signature_create", "signature_verify", "check_limits");*/
        $queueTitles = array("send_mail", "line_recognize", "signature_create", "signature_verify");
        foreach ($queueTitles as $queueTitle) {
            $output = array();
            exec("ps ax | grep " . $queueTitle . ".php | grep -v grep", $output);
            $queueStatus = "stopped";
            if (count($output) > 0) {
                $queueStatus = "running";
            }
            $queueList[] = array(
                "title" => $queueTitle,
                "title_translation" => GetTranslation("queue-" . $queueTitle),
                "count" => RabbitMQ::GetQueueCount($queueTitle),
                "status" => $queueStatus
            );
        }
        $this->SetProperty("QueueList", $queueList);

        $receiptList = new ReceiptList("receipt");

        $receiptOCRStatistics = $receiptList->GetReceiptListOCRStatisticsForDashboard(
            $dateFrom,
            $dateTo,
            $request->GetProperty("TimeGroup")
        );

        $this->SetProperty("ReceiptOCRStatisticsForGraph", $receiptOCRStatistics['graph'] ?? null);
        $this->SetProperty("ReceiptOCRStatisticsForLabelReceipt", $receiptOCRStatistics['labelReceipt'] ?? null);
        $this->SetProperty("ReceiptOCRStatisticsForLabelOcrRequest", $receiptOCRStatistics['labelOcrRequest'] ?? null);
        $this->AppendMessagesFromObject($receiptList);
    }

    /**
     * Loads voucher dashboard statistics
     *
     * @param LocalObject $request . Can contain following properties:
     *    Date - string - property for date field filtration
     *    yearly_statistics_date - string - property for date field filtration
     */
    public function LoadVoucherDashboard($request)
    {
        $year = $request->GetProperty("yearly_statistics_date");
        if (intval($request->GetProperty("yearly_statistics_date")) == 0) {
            $startOfYear = date("Y-01-01");
            $endOfYear = date("Y-12-31");
        } else {
            $startOfYear = date("Y-01-01", strtotime("01/01/" . $year));
            $endOfYear = date("Y-12-31", strtotime("01/01/" . $year));
        }

        //form month list with german/english month names
        $monthList = GetMonthList($startOfYear, $endOfYear, $request->GetProperty("LanguageCode"));

        //form statistics
        $statisticList = array();
        $productGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        foreach ($productGroupList as $productGroup) {
            $statisticList[] = array(
                "code" => $productGroup["code"],
                "group_id" => $productGroup["group_id"],
                "product-group-title" => GetTranslation("product-group-" . $productGroup["code"], "product"),
                "type_list" => array(
                    array(
                        "type" => "issued",
                        "title" => GetTranslation("issued"),
                        "month_list" => array(),
                        "total" => 0
                    ),
                    array(
                        "type" => "invoiced",
                        "title" => GetTranslation("invoiced"),
                        "title_issued_month" => GetTranslation("invoiced-issued-month"),
                        "title_not_issued_month" => GetTranslation("invoiced-not-issued-month"),
                        "month_list" => array(),
                        "total" => 0,
                        "total_issued_month" => 0,
                        "total_not_issued_month" => 0
                    ),
                    array(
                        "type" => "paid-to-employee",
                        "title" => GetTranslation("paid-to-employee"),
                        "month_list" => array(),
                        "total" => 0
                    ),
                    array(
                        "type" => "open",
                        "title" => GetTranslation("open"),
                        "title_not_used" => GetTranslation("open-not-used"),
                        "title_partially_used" => GetTranslation("open-partially-used"),
                        "month_list" => array(),
                        "total" => 0,
                        "total_not_used" => 0,
                        "total_partially_used" => 0
                    )
                )
            );
            $typeIssuedKey = 0;
            $typeInvoicedKey = 1;
            $typePaidKey = 2;
            $typeOpenedKey = 3;
            $key = count($statisticList) - 1;

            //include all months for table consistency
            $date = $startOfYear;

            while (strtotime($date) < strtotime($endOfYear)) {
                //Issued = the total Euro amount of new generated vouchers for this service in this month
                $amount = VoucherList::GetVoucherDashboardAmount(
                    false,
                    $productGroup["group_id"],
                    $date,
                    false,
                    "issued"
                );
                $statisticList[$key]["type_list"][$typeIssuedKey]["total"] += $amount;
                $statisticList[$key]["type_list"][$typeIssuedKey]["month_list"][] = array(
                    "month_id" => date("m", strtotime($date)),
                    "amount" => $amount
                );

                //Invoiced = total amount of all invoiced Euro values of the voucher invoices
                $amount = VoucherList::GetVoucherDashboardAmount(
                    false,
                    $productGroup["group_id"],
                    $date,
                    false,
                    "invoiced"
                );
                $statisticList[$key]["type_list"][$typeInvoicedKey]["total"] += $amount["amount"];
                $statisticList[$key]["type_list"][$typeInvoicedKey]["total_issued_month"] += $amount["issued_month"];
                $statisticList[$key]["type_list"][$typeInvoicedKey]["total_not_issued_month"] += $amount["not_issued_month"];
                $statisticList[$key]["type_list"][$typeInvoicedKey]["month_list"][] = array(
                    "month_id" => date("m", strtotime($date)),
                    "amount" => $amount["amount"],
                    "issued_month" => $amount["issued_month"],
                    "not_issued_month" => $amount["not_issued_month"]
                );

                //Paid to employee = total euro amount of the values per voucher service exported via the creditor export
                $amount = VoucherList::GetVoucherDashboardAmount(
                    false,
                    $productGroup["group_id"],
                    $date
                );
                $statisticList[$key]["type_list"][$typePaidKey]["total"] += $amount;
                $statisticList[$key]["type_list"][$typePaidKey]["month_list"][] = array(
                    "month_id" => date("m", strtotime($date)),
                    "amount" => $amount
                );

                //Open = sum of open Euro amounts of all existing vouchers per service which are still open in the system
                $amount = VoucherList::GetVoucherDashboardAmount(
                    false,
                    $productGroup["group_id"],
                    $date,
                    false,
                    "open"
                );
                $statisticList[$key]["type_list"][$typeOpenedKey]["total"] += $amount["amount"];
                $statisticList[$key]["type_list"][$typeOpenedKey]["total_not_used"] += $amount["not_used_amount"];
                $statisticList[$key]["type_list"][$typeOpenedKey]["total_partially_used"] += $amount["partially_used_amount"];
                $statisticList[$key]["type_list"][$typeOpenedKey]["month_list"][] = array(
                    "month_id" => date("m", strtotime($date)),
                    "amount" => $amount == 0 ? "" : $amount["amount"],
                    "not_used_amount" => $amount["not_used_amount"],
                    "partially_used_amount" => $amount["partially_used_amount"]
                );

                $date = date("Y-m-01", strtotime($date . " + 1 month"));
            }
        }

        $this->SetProperty("MonthTitleList", $monthList);
        $this->SetProperty("StatisticsList", $statisticList);
    }

    /**
     * Loads voucher dashboard reports
     *
     * @param LocalObject $request . Can contain following properties:
     *    company_unit_id - int - company unit id
     *    voucher_type - string - voucher service
     *    start_date - date
     *    end_date - date
     */
    public function LoadVoucherReports($request)
    {
        $reportList = array();
        $totalAmount = array();

        if ($request->IsPropertySet("company_unit_id")) {
            $companyUnitIDs = $request->GetProperty("company_unit_id");

            $mainProductIDs = array();
            if ($request->GetProperty("voucher_type") == "all" || $request->GetProperty("voucher_type") == "") {
                $productGroupList = ProductGroupList::GetProductGroupList(false, false, false, true);
                $productGroupIDs = array_column($productGroupList, "group_id");
                foreach ($productGroupList as $productGroup) {
                    $productGroupID = $productGroup["group_id"];
                    $specificProductGroup = SpecificProductGroupFactory::Create($productGroup["group_id"]);
                    $mainProductIDs[$productGroupID] = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
                }
            } else {
                $productGroupID = $request->GetProperty("voucher_type");
                $productGroupIDs = array($productGroupID);

                $specificProductGroup = SpecificProductGroupFactory::Create($productGroupID);
                $mainProductIDs[$productGroupID] = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
            }

            $startDate = $request->GetProperty("start_date");
            $endDate = $request->GetProperty("end_date");

            $totalPaid = 0;
            $totalToBePaid = 0;
            $totalOpen = 0;
            $totalDeactivated = 0;
            $totalExpired = 0;

            foreach ($companyUnitIDs as $companyUnitID) {
                $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
                $employeeList = array();

                $companyPaid = 0;
                $companyToBePaid = 0;
                $companyOpen = 0;
                $companyDeactivated = 0;
                $companyExpired = 0;

                foreach ($employeeIDs as $employeeID) {
                    $employeePaid = 0;
                    $employeeToBePaid = 0;
                    $employeeOpen = 0;
                    $employeeDeactivated = 0;
                    $employeeExpired = 0;

                    $employeePaidList = [];
                    $employeeToBePaidList = [];
                    $employeeOpenList = [];
                    $employeeDeactivatedList = [];
                    $employeeExpiredList = [];

                    $show = false;
                    foreach ($productGroupIDs as $productGroupID) {
                        $contract = new Contract("product");
                        if (
                            !$contract->ContractExistWithoutDate(
                                OPTION_LEVEL_EMPLOYEE,
                                $employeeID,
                                $mainProductIDs[$productGroupID]
                            )
                        ) {
                            continue;
                        }

                        $employeePaidResult = VoucherList::GetVoucherDashboardAmount(
                            true,
                            $productGroupID,
                            $startDate,
                            $endDate,
                            "paid",
                            $employeeID
                        );
                        $employeePaidList = array_merge($employeePaidList, $employeePaidResult["list"]);
                        $employeePaid += $employeePaidResult["amount"];

                        $employeeToBePaidResult = VoucherList::GetVoucherDashboardAmount(
                            true,
                            $productGroupID,
                            $startDate,
                            $endDate,
                            "to_be_paid",
                            $employeeID
                        );
                        $employeeToBePaidList = array_merge($employeeToBePaidList, $employeeToBePaidResult["list"]);
                        $employeeToBePaid += $employeeToBePaidResult["amount"];

                        $employeeOpenResult = VoucherList::GetVoucherDashboardAmount(
                            true,
                            $productGroupID,
                            $startDate,
                            $endDate,
                            "open",
                            $employeeID
                        );
                        $employeeOpenList = array_merge($employeeOpenList, $employeeOpenResult["list"]);
                        $employeeOpen += $employeeOpenResult["amount"];

                        if ($productGroupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
                            $employeeDeactivatedResult = VoucherList::GetInvoicedAndDeactivatedAmount(
                                $startDate,
                                $endDate,
                                $employeeID
                            );
                            $employeeDeactivatedList = array_merge(
                                $employeeDeactivatedList,
                                $employeeDeactivatedResult["list"]
                            );
                            $employeeDeactivated += $employeeDeactivatedResult["amount"];
                        }

                        $employeeExpiredResult = VoucherList::GetVoucherDashboardAmount(
                            true,
                            $productGroupID,
                            $startDate,
                            $endDate,
                            "expired",
                            $employeeID
                        );
                        $employeeExpiredList = array_merge($employeeExpiredList, $employeeExpiredResult["list"]);
                        $employeeExpired += $employeeExpiredResult["amount"];
                        $show = true;
                    }

                    if (!$show) {
                        continue;
                    }

                    $employeeList[] = array(
                        "employee_id" => $employeeID,
                        "employee_name" => Employee::GetNameByID($employeeID),
                        "paid" => $employeePaid,
                        "to_be_paid" => $employeeToBePaid,
                        "open" => $employeeOpen,
                        "deactivated" => $employeeDeactivated,
                        "expired" => $employeeExpired,
                        "paid_list" => $employeePaidList,
                        "to_be_paid_list" => $employeeToBePaidList,
                        "open_list" => $employeeOpenList,
                        "deactivated_list" => $employeeDeactivatedList,
                        "expired_list" => $employeeExpiredList
                    );

                    $companyPaid += $employeePaid;
                    $companyToBePaid += $employeeToBePaid;
                    $companyOpen += $employeeOpen;
                    $companyDeactivated += $employeeDeactivated;
                    $companyExpired += $employeeExpired;
                }

                $reportList[] = array(
                    "company_unit_id" => $companyUnitID,
                    "company_unit_title" => CompanyUnit::GetTitleByID($companyUnitID),
                    "employee_list" => $employeeList,
                    "paid" => $companyPaid,
                    "to_be_paid" => $companyToBePaid,
                    "open" => $companyOpen,
                    "deactivated" => $companyDeactivated,
                    "expired" => $companyExpired
                );

                $totalPaid += $companyPaid;
                $totalToBePaid += $companyToBePaid;
                $totalOpen += $companyOpen;
                $totalDeactivated += $companyDeactivated;
                $totalExpired += $companyExpired;
            }

            $totalAmount[] = array(
                "paid" => $totalPaid,
                "to_be_paid" => $totalToBePaid,
                "open" => $totalOpen,
                "deactivated" => $totalDeactivated,
                "expired" => $totalExpired
            );
        }

        $this->SetProperty("ReportList", $reportList);
        $this->SetProperty("TotalAmount", $totalAmount);
    }

    public function LoadVatReport($request)
    {
        if (!$request->IsPropertySet("voucher_type")) {
            return;
        }
        $receiptList = ReceiptList::GetVatReport($request);

        $exportedTotal = 0;
        $exportedDiffTotal = 0;
        $approvedTotal = 0;
        $approvedDiffTotal = 0;

        $reportList = [];
        foreach ($receiptList["exported"] as $exported) {
            $diff = $exported["sum"] - $exported["percentage_sum"];
            $reportList[] = [
                "vat" => $exported["vat"],
                "exported_sum" => $exported["sum"],
                "exported_percentage" => $exported["percentage_sum"],
                "exported_difference" => $diff,
                "approved_sum" => 0,
                "approved_percentage" => 0,
                "approved_difference" => 0,
            ];
            $exportedTotal += $exported["sum"];
            $exportedDiffTotal += $diff;
        }

        foreach ($receiptList["approved"] as $approved) {
            $vatList = array_column($reportList, "vat");
            $key = array_search($approved["vat"], $vatList);
            if ($key === false) {
                $reportList[] = [
                    "vat" => $approved["vat"],
                    "exported_sum" => 0,
                    "exported_percentage" => 0,
                    "exported_difference" => 0,
                ];
                $key = count($reportList) - 1;
            }

            $diff = $approved["sum"] - $approved["percentage_sum"];
            $reportList[$key]["approved_sum"] = $approved["sum"];
            $reportList[$key]["approved_percentage"] = $approved["percentage_sum"];
            $reportList[$key]["approved_difference"] = $diff;
            $approvedTotal += $approved["sum"];
            $approvedDiffTotal += $diff;
        }

        foreach ($reportList as $key => $report) {
            $reportList[$key]["sum_check"] = $report["exported_sum"]
                - $report["approved_sum"];
            $reportList[$key]["difference_check"] = $report["exported_difference"]
                - $report["approved_difference"];
            $reportList[$key]["percentage_check"] = $report["exported_percentage"]
                - $report["approved_percentage"];
        }
        $reportList[] = [
            "vat" => "total",
            "exported" => $exportedTotal,
            "exported_difference" => $exportedDiffTotal,
            "approved" => $approvedTotal,
            "approved_difference" => $approvedDiffTotal,
            "difference_check" => $exportedDiffTotal - $approvedDiffTotal,
        ];

        $this->SetProperty("ReportList", $reportList);
    }

    public function LoadProcessingDashboard($request)
    {
        $periodList = array();

        $elapsedTimeFormat = array(
            "days" => 0,
            "hours" => 0,
            "min" => 0,
            "sec" => 0,
            "millisec" => 0
        );

        if ($request->GetProperty("period_group") == "calendar") {
            $format = "Y-m-d";

            $dates = explode(" - ", $request->GetProperty("filter_statistics_range"));
            $startDate = $dates[0];
            $endDate = new DateTime($dates[1]);
            $endDate = $endDate->modify('+1 day')->format($format);

            $period = new DatePeriod(
                new DateTime($startDate),
                new DateInterval('P1D'),
                new DateTime($endDate)
            );

            foreach ($period as $key => $day) {
                $periodList[] = array(
                    "title" => FormatDateGerman($day->format($format), $request->GetProperty("LanguageCode"))
                );

                $valueListAmount[$day->format($format)] = array("value" => 0);
                $valueListTime[$day->format($format)] = array("value" => $elapsedTimeFormat);
                $valueListTimeForDetails[$day->format($format)] = array("value" => "");
                $valueListReceiptCount[$day->format($format)] = array("value" => 0);
            }
        } else {
            if (intval($request->GetProperty("yearly_statistics_date")) == 0) {
                $startDate = date("Y-01-01");
                $endDate = date("Y-12-31");
            } else {
                $startDate = date("Y-01-01", strtotime("01/01/" . $request->GetProperty("yearly_statistics_date")));
                $endDate = date("Y-12-31", strtotime("01/01/" . $request->GetProperty("yearly_statistics_date")));
            }

            $format = "n";
            $periodList = GetMonthList($startDate, $endDate, $request->GetProperty("LanguageCode"));

            $valueListAmount = array_fill(1, count($periodList), array("value" => 0));
            $valueListTime = array_fill(1, count($periodList), array("value" => $elapsedTimeFormat));
            $valueListTimeForDetails = array_fill(1, count($periodList), array("value" => ""));
            $valueListReceiptCount = array_fill(1, count($periodList), array("value" => 0));
        }

        //get receipt verification history
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM receipt_verification_history WHERE created_at >= " . Connection::GetSQLDateTime($startDate) . " AND
                        created_at <= " . Connection::GetSQLDateTime($endDate);

        $receiptVerificationList = $stmt->FetchList($query);

        $statisticList = array();
        $productGroupList = ProductGroupList::GetProductGroupList(false, false, "Y");
        $users = array_unique(array_column($receiptVerificationList, "user_id"));

        //form statistic grouped by services
        if ($request->GetProperty("grouped_by") == "service") {
            foreach ($users as $userID) {
                if (strlen(trim(User::GetNameByID($userID))) <= 0) {
                    continue;
                }

                $detailsListAmount[] = array(
                    "id" => $userID,
                    "name" => User::GetNameByID($userID),
                    "value_list" => $valueListAmount,
                    "total" => 0
                );
                $detailsListTime[] = array(
                    "id" => $userID,
                    "name" => User::GetNameByID($userID),
                    "value_list" => $valueListTimeForDetails,
                    "total" => $elapsedTimeFormat
                );
                $detailsListReceiptCount[] = array(
                    "id" => $userID,
                    "name" => User::GetNameByID($userID),
                    "value_list" => $valueListReceiptCount,
                    "total" => 0
                );
            }

            foreach ($productGroupList as $productGroup) {
                $statisticList[] = array(
                    "group_by" => "service",
                    "title" => GetTranslation("product-group-" . $productGroup["code"], "product"),
                    "group_id" => $productGroup["group_id"],
                    "total_type" => array(
                        array(
                            "item_id" => $productGroup["group_id"],
                            "title" => GetTranslation("amount_processing"),
                            "total_list" => $valueListAmount,
                            "details_list" => $detailsListAmount,
                            "total" => 0
                        ),
                        array(
                            "item_id" => $productGroup["group_id"],
                            "title" => GetTranslation("time_processing"),
                            "total_list" => $valueListTime,
                            "details_list" => $detailsListTime,
                            "total" => $elapsedTimeFormat
                        ),
                        array(
                            "item_id" => $productGroup["group_id"],
                            "title" => GetTranslation("count_processing"),
                            "total_list" => $valueListReceiptCount,
                            "details_list" => $detailsListReceiptCount,
                            "total" => 0
                        )
                    )
                );
            }
        } //form statistic grouped by employees
        else {
            foreach ($productGroupList as $productGroup) {
                $detailsListAmount[] = array(
                    "id" => $productGroup["group_id"],
                    "name" => GetTranslation("product-group-" . $productGroup["code"], "product"),
                    "value_list" => $valueListAmount,
                    "total" => 0
                );
                $detailsListTime[] = array(
                    "id" => $productGroup["group_id"],
                    "name" => GetTranslation("product-group-" . $productGroup["code"], "product"),
                    "value_list" => $valueListTimeForDetails,
                    "total" => $elapsedTimeFormat
                );
                $detailsListReceiptCount[] = array(
                    "id" => $productGroup["group_id"],
                    "name" => GetTranslation("product-group-" . $productGroup["code"], "product"),
                    "value_list" => $valueListReceiptCount,
                    "total" => 0
                );
            }

            foreach ($users as $userID) {
                $statisticList[] = array(
                    "group_by" => "user",
                    "title" => User::GetNameByID($userID),
                    "group_id" => $userID,
                    "total_type" => array(
                        array(
                            "item_id" => $userID,
                            "title" => GetTranslation("amount_processing"),
                            "total_list" => $valueListAmount,
                            "details_list" => $detailsListAmount,
                            "total" => 0
                        ),
                        array(
                            "item_id" => $userID,
                            "title" => GetTranslation("time_processing"),
                            "total_list" => $valueListTime,
                            "details_list" => $detailsListTime,
                            "total" => $elapsedTimeFormat
                        ),
                        array(
                            "item_id" => $userID,
                            "title" => GetTranslation("count_processing"),
                            "total_list" => $valueListReceiptCount,
                            "details_list" => $detailsListReceiptCount,
                            "total" => 0
                        )
                    )
                );
            }
        }

        //compose value lists
        $processingList = [];

        $receiptIDs = array_column($receiptVerificationList, "receipt_id");
        $stmt = GetStatement();
        $tmpGroupIDs = $stmt->FetchList("SELECT receipt_id, group_id FROM receipt WHERE receipt_id IN (" . implode(
            ", ",
            $receiptIDs
        ) . ")");
        $groupIDs = [];
        foreach ($tmpGroupIDs as $group) {
            $groupIDs[$group["receipt_id"]] = $group["group_id"];
        }

        foreach ($receiptVerificationList as $verification) {
            $productGroupID = $groupIDs[$verification["receipt_id"]] ?? 0;

            $period = date($format, strtotime($verification['opening_receipt_at']));

            $openingTime = new DateTime($verification['opening_receipt_at']);
            $saveTime = new DateTime($verification['saved_receipt_at']);
            $diffTime = $saveTime->diff($openingTime);

            if ($request->GetProperty("grouped_by") == "service") {
                if (!isset($processingList[$productGroupID][$verification["user_id"]]["amount"][$period])) {
                    $processingList[$productGroupID][$verification["user_id"]]["amount"][$period] = 0;
                }
                if (!isset($processingList[$productGroupID][$verification["user_id"]]["receipt_count"][$period])) {
                    $processingList[$productGroupID][$verification["user_id"]]["receipt_count"][$period] = 0;
                }
                if (!isset($processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period])) {
                    $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period] = [
                        "days" => 0,
                        "hours" => 0,
                        "min" => 0,
                        "sec" => 0,
                        "millisec" => 0,
                    ];
                }

                $processingList[$productGroupID][$verification["user_id"]]["amount"][$period]
                    += $verification['amount'];
                $processingList[$productGroupID][$verification["user_id"]]["receipt_count"][$period]++;

                $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period]["days"]
                    += (int)$diffTime->format("%a");
                $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period]["hours"]
                    += (int)$diffTime->format("%h");
                $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period]["min"]
                    += (int)$diffTime->format("%i");
                $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period]["sec"]
                    += (int)$diffTime->format("%s");
                $processingList[$productGroupID][$verification["user_id"]]["elapsed_time"][$period]["millisec"]
                    += abs(round((int)$diffTime->format("%f") / 1000));
            } else {
                if (!isset($processingList[$verification["user_id"]][$productGroupID]["amount"][$period])) {
                    $processingList[$verification["user_id"]][$productGroupID]["amount"][$period] = 0;
                }
                if (!isset($processingList[$verification["user_id"]][$productGroupID]["receipt_count"][$period])) {
                    $processingList[$verification["user_id"]][$productGroupID]["receipt_count"][$period] = 0;
                }

                $processingList[$verification["user_id"]][$productGroupID]["amount"][$period] += $verification['amount'];
                $processingList[$verification["user_id"]][$productGroupID]["receipt_count"][$period] += 1;

                $processingList[$verification["user_id"]][$productGroupID]["elapsed_time"][$period]["days"] += (int)$diffTime->format("%a");
                $processingList[$verification["user_id"]][$productGroupID]["elapsed_time"][$period]["hours"] += (int)$diffTime->format("%h");
                $processingList[$verification["user_id"]][$productGroupID]["elapsed_time"][$period]["min"] += (int)$diffTime->format("%i");
                $processingList[$verification["user_id"]][$productGroupID]["elapsed_time"][$period]["sec"] += (int)$diffTime->format("%s");
                $processingList[$verification["user_id"]][$productGroupID]["elapsed_time"][$period]["millisec"] += abs(round((int)$diffTime->format("%f") / 1000));
            }
        }

        $countStatistic = count($statisticList);
        //insert value lists into statistic
        for ($i = 0; $i < $countStatistic; $i++) {
            $groupID = $statisticList[$i]["group_id"];
            //amount
            $countAmount = count($statisticList[$i]["total_type"][0]["details_list"]);
            for ($j = 0; $j < $countAmount; $j++) {
                $itemID = $statisticList[$i]["total_type"][0]["details_list"][$j]["id"];
                if (empty($processingList[$groupID][$itemID]["amount"])) {
                    continue;
                }

                foreach ($processingList[$groupID][$itemID]["amount"] as $period => $amount) {
                    $statisticList[$i]["total_type"][0]["details_list"][$j]["value_list"][$period]["value"] = $amount;
                    $statisticList[$i]["total_type"][0]["details_list"][$j]["total"] += $amount;
                    $statisticList[$i]["total_type"][0]["total_list"][$period]["value"] += $amount;
                    $statisticList[$i]["total_type"][0]["total"] += $amount;
                }
            }

            //time
            $countTime = count($statisticList[$i]["total_type"][1]["details_list"]);
            for ($j = 0; $j < $countTime; $j++) {
                $itemID = $statisticList[$i]["total_type"][1]["details_list"][$j]["id"];

                if (!empty($processingList[$groupID][$itemID]["elapsed_time"])) {
                    foreach ($processingList[$groupID][$itemID]["elapsed_time"] as $period => $time) {
                        $statisticList[$i]["total_type"][1]["details_list"][$j]["value_list"][$period]["value"] = self::PrepareElapsedTime($time);

                        foreach ($elapsedTimeFormat as $key => $value) {
                            $statisticList[$i]["total_type"][1]["details_list"][$j]["total"][$key] += $time[$key];
                            $statisticList[$i]["total_type"][1]["total_list"][$period]["value"][$key] += $time[$key];
                            $statisticList[$i]["total_type"][1]["total"][$key] += $time[$key];
                        }
                    }
                }
                $statisticList[$i]["total_type"][1]["details_list"][$j]["total"] = self::PrepareElapsedTime($statisticList[$i]["total_type"][1]["details_list"][$j]["total"]);
            }

            foreach ($statisticList[$i]["total_type"][1]["total_list"] as $period => $time) {
                $statisticList[$i]["total_type"][1]["total_list"][$period]["value"] = self::PrepareElapsedTime($time["value"]);
            }

            $statisticList[$i]["total_type"][1]["total"] = self::PrepareElapsedTime($statisticList[$i]["total_type"][1]["total"]);

            //receipts count
            $countReceiptsCount = count($statisticList[$i]["total_type"][2]["details_list"]);
            for ($j = 0; $j < $countReceiptsCount; $j++) {
                $itemID = $statisticList[$i]["total_type"][2]["details_list"][$j]["id"];

                if (!empty($processingList[$groupID][$itemID]["receipt_count"])) {
                    foreach ($processingList[$groupID][$itemID]["receipt_count"] as $period => $count) {
                        $statisticList[$i]["total_type"][2]["details_list"][$j]["value_list"][$period]["value"] = $count;
                        $statisticList[$i]["total_type"][2]["details_list"][$j]["total"] += $count;
                        $statisticList[$i]["total_type"][2]["total_list"][$period]["value"] += $count;
                        $statisticList[$i]["total_type"][2]["total"] += $count;
                    }
                }
                if ($statisticList[$i]["group_by"] != "service" || $statisticList[$i]["total_type"][2]["details_list"][$j]["total"] != 0) {
                    continue;
                }

                unset($statisticList[$i]["total_type"][0]["details_list"][$j]);
                unset($statisticList[$i]["total_type"][1]["details_list"][$j]);
                unset($statisticList[$i]["total_type"][2]["details_list"][$j]);
            }
            if ($statisticList[$i]["group_by"] != "user" || $statisticList[$i]["total_type"][2]["total"] != 0) {
                continue;
            }

            unset($statisticList[$i]);
        }

        $this->SetProperty("period_list", $periodList);
        $this->SetProperty("statistics_list", $statisticList);
    }

    static function PrepareElapsedTime($dateTimeDiff)
    {
        $elapsedTime = array();

        if ($dateTimeDiff["millisec"] > 1000) {
            $dateTimeDiff["sec"] += floor($dateTimeDiff["millisec"] / 1000);
            $dateTimeDiff["millisec"] %= 1000;
        }

        if ($dateTimeDiff["sec"] > 60) {
            $dateTimeDiff["min"] += floor($dateTimeDiff["sec"] / 60);
            $dateTimeDiff["sec"] %= 60;
        }

        if ($dateTimeDiff["min"] > 60) {
            $dateTimeDiff["hours"] += floor($dateTimeDiff["min"] / 60);
            $dateTimeDiff["min"] %= 60;
        }

        if ($dateTimeDiff["hours"] > 24) {
            $dateTimeDiff["days"] += floor($dateTimeDiff["hours"] / 24);
            $dateTimeDiff["hours"] %= 24;
        }

        if ($dateTimeDiff["days"] > 0) {
            $elapsedTime[] = $dateTimeDiff["days"] . "d";
        }

        if ($dateTimeDiff["hours"] > 0) {
            $elapsedTime[] = $dateTimeDiff["hours"] . "h";
        }

        if ($dateTimeDiff["min"] > 0) {
            $elapsedTime[] = $dateTimeDiff["min"] . "m";
        }

        if ($dateTimeDiff["sec"] > 0) {
            $elapsedTime[] = $dateTimeDiff["sec"] . "s";
        }

        if ($dateTimeDiff["millisec"] > 0) {
            $elapsedTime[] = $dateTimeDiff["millisec"] . "ms";
        }

        return implode(" ", $elapsedTime);
    }
}
