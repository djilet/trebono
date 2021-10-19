<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReceiptList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ReceiptList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "admin" => "t.ord, r.created ASC",
            "admin_desc" => "r.created DESC, t.ord ASC",
            "service_asc_document_date_desc" => "g.sort_order ASC, r.document_date DESC NULLS LAST",
            "denial_reason_employee_id_asc" => "r.denial_reason, r.employee_id ASC",
            "employee_id_denial_reason_asc" => "r.employee_id, r.denial_reason ASC",
            "denial_reason_employee_id_asc" => "r.denial_reason, r.employee_id ASC",
            "admin_document_date" => "t.ord, r.document_date, r.created ASC",
            "api" => "has_unread_comment_employee DESC, t.ord ASC, r.created DESC",
            "created_asc" => "r.created ASC",
            "created_desc" => "r.created DESC",
            "document_date_asc" => "r.document_date ASC",
            "document_date_desc" => "r.document_date DESC"
        ));
        $this->SetOrderBy("created_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads receipt list for admin panel
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *        <li><u>FilterCreatedRange</u> - string - property for "created" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterStatus</u> - string - property for "status" field filtration</li>
     *        <li><u>FilterLegalReceiptID</u> - int - property for "legal_receipt_id" field filtration</li>
     *        <li><u>FilterName</u> - string - property for employee name filtration</li>
     *        <li><u>FilterEmployeeID</u> - string - property for employee id filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     * @param string $permissionName
     * @param bool $prepareBeforeShow if false, don't prepare content
     */
    public function LoadReceiptListForAdmin($request, $fullList = false, $permissionName = "receipt")
    {
        $groupMap = array(
            "Employee" => array(
                "Key" => "employee_id",
                "OrderByKey" => "employee_id_denial_reason_asc",
                "GroupTitle" => "employee_name",
                "TranslationKey" => "receipt-group-employee"
            ),
            "DenialReason" => array(
                "Key" => "denial_reason",
                "OrderByKey" => "denial_reason_employee_id_asc",
                "GroupTitle" => "denial_reason",
                "TranslationKey" => "receipt-group-denial-reason"
            )
        );
        if ($request->GetProperty("GroupBy")) {
            $this->SetOrderBy($groupMap[$request->GetProperty("GroupBy")]["OrderByKey"]);
        }

        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = $this->GetSQLConditionsForAdmin($request, $permissionName);
        if (!is_array($where)) {
            return;
        }

        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.group_id, r.employee_id, r.created, r.updated, 
						r.status, r.status_updated, r.archive, r.booked, r.trip_id,
						r.document_date, r.real_amount_approved,
						g.code AS group_code, g.title AS group_title, 
						c.unread_comment_count_admin, c.unread_comment_count_employee, c.chat, r.denial_reason, r.status_user_id
					FROM receipt AS r 
						LEFT JOIN product_group AS g ON g.group_id=r.group_id 
						LEFT JOIN (SELECT receipt_id,
						    SUM(CASE read_by_admin WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_admin, 
						    SUM(CASE read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee,
						    COUNT(comment_id) AS chat 
						  FROM receipt_comment GROUP BY receipt_id) c ON c.receipt_id=r.receipt_id
						JOIN unnest('{\"new\",\"review\",\"supervisor\",\"approve_proposed\",\"approved\",\"denied\"}'::text[]) with ordinality as t(status, ord) using (status)"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query);

        $this->PrepareContentBeforeShow();

        if ($request->GetProperty("GroupBy")) {
            $groupByID = 0;
            for ($i = 0; $i < $this->GetCountItems(); $i++) {
                $this->_items[$i]["GroupList"] = array();
                $addChildGroup = false;
                if ($i == 0 || $addChildGroup || $this->_items[$i][$groupMap[$request->GetProperty("GroupBy")]["Key"]] != $this->_items[$i - 1][$groupMap[$request->GetProperty("GroupBy")]["Key"]]) {
                    $count = 1;
                    for ($j = $i + 1; $j < $this->GetCountItems(); $j++) {
                        if ($this->_items[$j][$groupMap[$request->GetProperty("GroupBy")]["Key"]] != $this->_items[$j - 1][$groupMap[$request->GetProperty("GroupBy")]["Key"]]) {
                            break;
                        }

                        $count++;
                    }

                    $groupByID++;
                    $this->_items[$i]["GroupList"][] = array(
                        "group" => 1,
                        "group_title" => $this->_items[$i][$groupMap[$request->GetProperty("GroupBy")]["GroupTitle"]],
                        "group_translation" => GetTranslation(
                            $groupMap[$request->GetProperty("GroupBy")]["TranslationKey"],
                            $this->module
                        ),
                        "group_by_id" => $groupByID,
                        "group_count" => $count
                    );
                    $addChildGroup = true;
                }
                $this->_items[$i]["group_by_id"] = $groupByID;
            }
        }

        if ($request->GetProperty("AppendChatInfo") != "Y") {
            return;
        }

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $receiptCommentList = new ReceiptCommentList($this->module);
            $receiptCommentList->LoadCommentListForAdmin($this->_items[$i]["receipt_id"]);
            //we want to get info from the latest comment (list is sorted by created DESC)
            if (empty($receiptCommentList->_items[0])) {
                continue;
            }

            $this->_items[$i]["last_comment_created"] = $receiptCommentList->_items[0]["created"];
            $this->_items[$i]["comment_list"] = $receiptCommentList->GetItems();
        }
    }

    /**
     * Loads short version of receipt list for admin panel
     *
     * @param string $field field to fetch
     * @param LocalObject $request
     * @param string $permissionName
     */
    public function GetShortReceiptListForAdmin($field, $request, $permissionName = "receipt")
    {
        $where = $this->GetSQLConditionsForAdmin($request, $permissionName);
        if (!is_array($where)) {
            return;
        }

        $stmt = GetStatement(DB_MAIN);
        $where[] = "r." . $field . " IS NOT NULL";
        $query = "SELECT r." . $field . ", tr.trip_name
				FROM receipt AS r  
					LEFT JOIN trip as tr ON r.trip_id=tr.trip_id
					LEFT JOIN (SELECT receipt_id, 
						SUM(CASE read_by_admin WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_admin, 
						SUM(CASE read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee,
						COUNT(comment_id) AS chat 
					  FROM receipt_comment GROUP BY receipt_id) c ON c.receipt_id=r.receipt_id
					JOIN unnest('{\"new\",\"review\",\"supervisor\",\"approve_proposed\",\"approved\",\"denied\"}'::text[]) with ordinality as t(status, ord) using (status)"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $receiptList = array_unique($stmt->FetchList($query), SORT_REGULAR);

        //sorting version list for more convenience
        array_multisort(array_column($receiptList, $field), SORT_ASC, $receiptList);

        return $receiptList;
    }

    /**
     * Loads receipt list for admin panel
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *        <li><u>FilterCreatedRange</u> - string - property for "created" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterStatus</u> - string - property for "status" field filtration</li>
     *        <li><u>FilterLegalReceiptID</u> - int - property for "legal_receipt_id" field filtration</li>
     *        <li><u>FilterName</u> - string - property for employee name filtration</li>
     *        <li><u>FilterEmployeeID</u> - string - property for employee id filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadReceiptListForDashboard($request, $prepare = false)
    {
        $groupMap = array(
            "Employee" => array(
                "Key" => "employee_id",
                "OrderByKey" => "employee_id_denial_reason_asc",
                "GroupTitle" => "employee_name",
                "TranslationKey" => "receipt-group-employee"
            ),
            "DenialReason" => array(
                "Key" => "denial_reason",
                "OrderByKey" => "denial_reason_employee_id_asc",
                "GroupTitle" => "denial_reason",
                "TranslationKey" => "receipt-group-denial-reason"
            )
        );
        if ($request->GetProperty("GroupBy")) {
            $this->SetOrderBy($groupMap[$request->GetProperty("GroupBy")]["OrderByKey"]);
        }

        $this->SetItemsOnPage(0);

        $where = array();
        if ($request->GetProperty("FilterCreatedFrom")) {
            $where[] = "DATE(r.created) >= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedFrom"));
        }
        if ($request->GetProperty("FilterCreatedTo")) {
            $where[] = "DATE(r.created) <= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedTo"));
        }
        if ($request->GetProperty("FilterStatus")) {
            $where[] = "r.status IN(" . implode(
                ", ",
                Connection::GetSQLArray($request->GetProperty("FilterStatus"))
            ) . ")";
        }
        if ($request->GetProperty("FilterEmployeeID") && $request->ValidateNotEmpty("FilterEmployeeID")) {
            $where[] = "r.employee_id=" . $request->GetIntProperty("FilterEmployeeID");
        }
        if ($request->GetProperty("FilterDenialReason")) {
            $where[] = "r.denial_reason LIKE '%" . $request->GetProperty("FilterDenialReason") . "%'";
        }

        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.group_id, r.employee_id, r.created, r.updated,
				r.status, r.status_updated, r.archive,
				r.document_date, r.real_amount_approved,
				g.code AS group_code, g.title AS group_title,
				c.unread_comment_count_admin, c.unread_comment_count_employee, c.chat, r.denial_reason, r.status_user_id
			FROM receipt AS r
				LEFT JOIN product_group AS g ON g.group_id=r.group_id
				LEFT JOIN (SELECT receipt_id,
				    SUM(CASE read_by_admin WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_admin,
				    SUM(CASE read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee,
				    COUNT(comment_id) AS chat
				  FROM receipt_comment GROUP BY receipt_id) c ON c.receipt_id=r.receipt_id
				JOIN unnest('{\"new\",\"review\",\"supervisor\",\"approve_proposed\",\"approved\",\"denied\"}'::text[]) with ordinality as t(status, ord) using (status)"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);

        if ($prepare) {
            $this->PrepareContentBeforeShow();
        }

        if (!$request->GetProperty("GroupBy")) {
            return;
        }

        $groupByID = 0;
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["GroupList"] = array();
            $addChildGroup = false;
            if ($i == 0 || $addChildGroup || $this->_items[$i][$groupMap[$request->GetProperty("GroupBy")]["Key"]] != $this->_items[$i - 1][$groupMap[$request->GetProperty("GroupBy")]["Key"]]) {
                $count = 1;
                for ($j = $i + 1; $j < $this->GetCountItems(); $j++) {
                    if ($this->_items[$j][$groupMap[$request->GetProperty("GroupBy")]["Key"]] != $this->_items[$j - 1][$groupMap[$request->GetProperty("GroupBy")]["Key"]]) {
                        break;
                    }

                    $count++;
                }

                $groupByID++;
                $this->_items[$i]["GroupList"][] = array(
                    "group" => 1,
                    "group_title" => $this->_items[$i][$groupMap[$request->GetProperty("GroupBy")]["GroupTitle"]],
                    "group_translation" => GetTranslation(
                        $groupMap[$request->GetProperty("GroupBy")]["TranslationKey"],
                        $this->module
                    ),
                    "group_by_id" => $groupByID,
                    "group_count" => $count
                );
                $addChildGroup = true;
            }
            $this->_items[$i]["group_by_id"] = $groupByID;
        }
    }

    /**
     * Prepares sql where-conditions by filter form and current user's acl
     *
     * @param LocalObject $request
     *
     * @return bool|string[] array of conditions or false if there is no employess found by name prefilter or acl
     */
    private function GetSQLConditionsForAdmin($request, $permissionName)
    {
        $where = array();

        //check for receipt permissions
        $user = new User();
        $user->LoadBySession();
        if (!$user->Validate(array($permissionName))) {
            $companyUnitIDs = $user->GetPermissionLinkIDs($permissionName);
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) <= 0) {
                return false;
            }

            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT employee_id FROM employee WHERE company_unit_id IN(" . implode(
                ", ",
                $companyUnitIDs
            ) . ")";
            $employeeIDs = array_keys($stmt->FetchIndexedList($query));
            if (count($employeeIDs) <= 0) {
                return false;
            }

            $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        }

        if ($permissionName == "tax_auditor" || $request->GetProperty("FilterWithoutVoucherReceipts")) {
            $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true);
            $where[] = "r.group_id NOT IN(" . implode(", ", array_column($voucherProductGroupList, "group_id")) . ")";
        }

        $hideFromAdmin = [
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS_VOUCHER),
        ];
        if (!$user->Validate(array("root"))) {
            $where[] = "r.group_id NOT IN (" . implode(", ", $hideFromAdmin) . ")";
        }

        $permissionService = "service";
        $productGroupIDs = array();
        if ($user->Validate(array($permissionService => null))) {
            $productGroupIDs = $user->GetPermissionLinkIDs($permissionService);
        }

        if (count($productGroupIDs) > 0) {
            $where[] = "r.group_id IN(" . implode(", ", $productGroupIDs) . ")";
        }

        //process filter params
        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "r.created >= " . Connection::GetSQLDateTime($from);
            $where[] = "r.created <= " . Connection::GetSQLDateTime($to.":59"); //:59 - seconds to search the minute fully
        }
        if ($request->GetProperty("FilterCreatedFrom")) {
            $where[] = "DATE(r.created) >= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedFrom"));
        }
        if ($request->GetProperty("FilterCreatedTo")) {
            $where[] = "DATE(r.created) <= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedTo"));
        }
        if ($request->GetProperty("FilterStatus")) {
            $where[] = "r.status IN(" . implode(
                ", ",
                Connection::GetSQLArray($request->GetProperty("FilterStatus"))
            ) . ")";
        }
        if ($request->GetProperty("FilterStatus1")) {
            $where[] = "r.status = '" . $request->GetProperty("FilterStatus1") . "'";
        }
        if ($request->GetProperty("FilterCreatedDate")) {
            $where[] = "DATE(r.created) =" . Connection::GetSQLDate($request->GetProperty("FilterCreatedDate")) . "";
        }
        if ($request->GetProperty("FilterDenialReason")) {
            $where[] = "r.denial_reason LIKE '%" . $request->GetProperty("FilterDenialReason") . "%'";
        }
        if ($request->GetProperty("FilterLegalReceiptID")) {
            $where[] = "r.legal_receipt_id=" . $request->GetIntProperty("FilterLegalReceiptID");
        }
        if ($request->GetProperty("FilterCreditorNumber")) {
            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT employee_id FROM employee WHERE creditor_number = '" . $request->GetIntProperty("FilterCreditorNumber") . "'";
            $employeeIDs = array_keys($stmt->FetchIndexedList($query));
            if (count($employeeIDs) <= 0) {
                return false;
            }

            $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        }
        if ($request->GetProperty("FilterVoucherID")) {
            if ($request->GetProperty("FilterProductGroup") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER)) {
                $stmt = GetStatement();
                $query = "SELECT v.receipt_ids
						FROM voucher AS v
						WHERE v.voucher_id=" . $request->GetProperty("FilterVoucherID");
                $receipt_IDs = array_keys($stmt->FetchIndexedList($query));
                if (count($receipt_IDs) <= 0) {
                    return false;
                }

                $where[] = "r.receipt_id IN(" . implode(", ", $receipt_IDs) . ")";
            }
            if ($request->GetProperty("FilterProductGroup") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS)) {
                $voucherID = $request->GetProperty("FilterVoucherID");
                $bonus = new SpecificProductGroupBonus();
                $stmt = GetStatement();
                $query = "SELECT r.employee_id 
						FROM receipt AS r
						WHERE r.group_id=" . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS);
                $employeeIDs = array_keys($stmt->FetchIndexedList($query));
                foreach ($employeeIDs as $employeeID) {
                    $voucherMap = $bonus->GetReceiptMappedVoucherList($employeeID);
                    $voucherMapKey = array_search($voucherID, array_column($voucherMap, "voucher_id"));
                    if ($voucherMapKey === false || !isset($voucherMap[$voucherMapKey]["receipt_list"])) {
                        continue;
                    }

                    foreach ($voucherMap[$voucherMapKey]["receipt_list"] as $key => $receipt) {
                        $receipt_IDs[] = $receipt['receipt_id'];
                    }
                }
                if (count($receipt_IDs) <= 0) {
                    return false;
                }

                $where[] = "r.receipt_id IN(" . implode(", ", $receipt_IDs) . ")";
            }
            if ($request->GetProperty("FilterProductGroup") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT)) {
                $voucherID = $request->GetProperty("FilterVoucherID");
                $gift = new SpecificProductGroupGift();
                $stmt = GetStatement();
                $query = "SELECT r.employee_id 
						FROM receipt AS r
						WHERE r.group_id=" . ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT);
                $employeeIDs = array_keys($stmt->FetchIndexedList($query));
                foreach ($employeeIDs as $employeeID) {
                    $voucherMap = $gift->GetReceiptMappedVoucherList($employeeID);
                    $voucherMapKey = array_search($voucherID, array_column($voucherMap, "voucher_id"));
                    if ($voucherMapKey === false || !isset($voucherMap[$voucherMapKey]["receipt_list"])) {
                        continue;
                    }

                    foreach ($voucherMap[$voucherMapKey]["receipt_list"] as $key => $receipt) {
                        $receipt_IDs[] = $receipt['receipt_id'];
                    }
                }
                if (count($receipt_IDs) <= 0) {
                    return false;
                }

                $where[] = "r.receipt_id IN(" . implode(", ", $receipt_IDs) . ")";
            }
        }
        if ($request->GetProperty("FilterName")) {
            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT e.employee_id 
						FROM employee AS e 
							JOIN user_info AS u ON u.user_id=e.user_id 
						WHERE CONCAT(" . Connection::GetSQLDecryption("u.first_name") . ", ' ', " . Connection::GetSQLDecryption("last_name") . ") ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterName"));
            $employeeIDs = array_keys($stmt->FetchIndexedList($query));
            if (count($employeeIDs) <= 0) {
                return false;
            }

            $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        }
        if ($request->GetProperty("FilterCompanyTitle")) {
            $stmt = GetStatement();
            $query = "SELECT company_unit_id FROM company_unit
						WHERE " . Connection::GetSQLDecryption("title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterCompanyTitle"));
            $companyUnitIDs = array_keys($stmt->FetchIndexedList($query));
            if (count($companyUnitIDs) <= 0) {
                return false;
            }

            $employeeIDs = array();
            foreach ($companyUnitIDs as $key => $value) {
                $employeeIDs = array_merge($employeeIDs, EmployeeList::GetEmployeeIDsByCompanyUnitID($value));
            }
            if (count($employeeIDs) > 0) {
                $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
            }
        }
        if ($request->GetProperty("FilterPayrollDate") && $request->ValidateNotEmpty("FilterEmployeeID")) {
            $companyUnitID = Employee::GetEmployeeField($request->GetProperty("FilterEmployeeID"), "company_unit_id");
            $payrollMonth = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "current_month" ||
                Payroll::PayrollExists($companyUnitID, $request->GetProperty("FilterPayrollDate"))
                ? date_create($request->GetProperty("FilterPayrollDate"))
                : date_create($request->GetProperty("FilterPayrollDate"))->modify("-1 month");
            $where[] = "r.datev_export = " . Connection::GetSQLString($payrollMonth->format("Ym"));
            $where[] = "r.pdf_export = " . Connection::GetSQLString($payrollMonth->format("Ym"));
            $where[] = "DATE(r.document_date) >= " . Connection::GetSQLDate($request->GetProperty("FilterPayrollDateFrom"));
            $where[] = "DATE(r.document_date) <= " . Connection::GetSQLDate($payrollMonth->format("Y-m-t"));
        }
        if ($request->GetProperty("FilterEmployeeID") && $request->ValidateNotEmpty("FilterEmployeeID")) {
            $where[] = "r.employee_id=" . $request->GetIntProperty("FilterEmployeeID");
        }
        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "r.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "r.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }
        if (
            $request->GetProperty("FilterHasUnreadMessagesAdmin") == "Y"
            && !$request->IsPropertySet("receipt_id")
        ) {
            $where[] = "c.unread_comment_count_admin>0";
        }
        if (
            $request->GetProperty("FilterHasUnreadMessagesEmployee") == "Y"
            && !$request->IsPropertySet("receipt_id")
        ) {
            $where[] = "c.unread_comment_count_employee>0";
        }
        if (
            $request->GetProperty("FilterHasChat") == "Y"
            && !$request->IsPropertySet("receipt_id")
        ) {
            $where[] = "c.chat > 0";
        }
        if ($request->GetProperty("FilterCreatedRangeChat")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRangeChat"));
            $stmt = GetStatement();
            $query = "SELECT receipt_id FROM receipt_comment WHERE 
                         created >= " . Connection::GetSQLDateTime($from)
                        . "AND created <= " . Connection::GetSQLDateTime($to.":59");  //:59 - seconds to search the minute fully
            $result = $stmt->FetchList($query);
            if (empty($result)) {
                return false;
            }

            $result = array_column($result, "receipt_id");
            $where[] = "r.receipt_id IN(" . implode(", ", $result) . ")";
        }
        if ($request->GetProperty("FilterAutomaticProcessed") == "Y") {
            $where[] = "r.automatic_processed='Y'";
        }
        if ($request->GetProperty("FilterProductGroup")) {
            $where[] = "r.group_id=" . $request->GetIntProperty("FilterProductGroup");
        }
        if ($request->GetProperty("FilterTripID")) {
            $where[] = "r.trip_id=" . $request->GetIntProperty("FilterTripID");
        }
        if ($request->GetProperty("FilterNotBooked") == "Y") {
            $where[] = "(r.booked = 'N' OR r.booked IS NULL)";
        }
        if ($request->GetProperty("FilterUserLastChangedStatus")) {
            $where[] = "r.status_user_id=" . $request->GetIntProperty("FilterUserLastChangedStatus");
            $where[] = "r.status<>'new'";
        }

        return $where;
    }

    /**
     * Returns receipt_id of next receipt in the admin receipt list. Uses the same filter properties as LoadReceiptListForAdmin
     *
     * @param LocalObject $request
     *
     * @return bool|int receipt_id of next receipt or false if current receipt_id is last or there are no any receipts
     *
     * @see ReceiptList::LoadReceiptListForAdmin
     */
    public function GetNextReceiptID($request)
    {
        $where = $this->GetSQLConditionsForAdmin($request, "receipt");
        if (!is_array($where)) {
            return false;
        }
        $stmt = GetStatement();
        $created = $stmt->FetchField("SELECT created FROM receipt WHERE receipt_id=" . $request->GetIntProperty("receipt_id"));

        $query = "SELECT r.receipt_id 
					FROM receipt AS r 
						LEFT JOIN receipt_comment AS c ON c.receipt_id=r.receipt_id 
						JOIN unnest('{\"new\",\"review\",\"supervisor\",\"approve_proposed\",\"approved\",\"denied\"}'::text[]) with ordinality as t(status, ord) using (status) 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " 
					GROUP BY r.receipt_id, t.ord";
        $receiptList = new ReceiptList($this->module);
        $receiptList->SetItemsOnPage(0);
        $receiptList->SetOrderBy("admin");
        $receiptList->LoadFromSQL($query);

        if ($receiptList->GetCountItems() > 0) {
            $receiptIDs = array_column($receiptList->GetItems(), "receipt_id");
            $currentReceiptIndex = array_search($request->GetProperty("receipt_id"), $receiptIDs);
            if ($currentReceiptIndex < count($receiptIDs) - 1) {
                return $receiptIDs[$currentReceiptIndex + 1];
            }
        }

        return false;
    }

    /**
     * Loads receipt list for api
     *
     * @param int $employeeID employee_id receipts to be filtered by
     * @param int $groupID group_id receipts to be filtered by
     * @param string $status status receipts to be filtered by
     * @param int $dateFrom receipt_date >= date_from to be filtered by
     * @param int $dateTo receipt_date <= date_to to be filtered by
     */
    public function LoadReceiptListForApi(
        $employeeID,
        $groupID,
        $status = false,
        $tripID = false,
        $dateFrom = false,
        $dateTo = false
    ) {
        $where = array();
        $where[] = "r.employee_id=" . intval($employeeID);
        if ($groupID) {
            $where[] = "r.group_id=" . intval($groupID);
        }

        if ($status) {
            $where[] = $status == "approved"
                ? "r.status IN ('approved', 'approve_proposed')"
                : "r.status=" . Connection::GetSQLString($status);
        }

        if ($tripID) {
            $where[] = "r.trip_id=" . intval($tripID);
        }

        $where[] = "r.archive='N'";

        $currentDate = GetCurrentDate();
        $currentMonthYear = date('Y-m', time());

        if ($dateFrom) {
            $where[] = "DATE(r.created) >= " . Connection::GetSQLDate($dateFrom);
        } else {
            $dateFrom = date("Y-m-01", strtotime($currentMonthYear . " -1 month"));
            $groupCode = ProductGroup::GetProductGroupCodeByID($groupID);
            switch ($groupCode) {
                case PRODUCT_GROUP__TRAVEL:
                    $where[] = "(DATE(r.created) >= " . Connection::GetSQLDate($dateFrom) . "
                     AND  tr.finished_by_employee='Y' OR tr.finished_by_employee='N')";
                    break;
                case PRODUCT_GROUP__FOOD:
                    $companyUnitID = Employee::GetEmployeeField($employeeID, "company_unit_id");
                    $payrollMonth = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID);
                    $currentMonthPayrollTime = strtotime(date("Y-m-" . intval(CompanyUnit::GetPropertyValue("financial_statement_date", $companyUnitID)) . " 17:59:00"));
                    $currentTime = time();
                    $lastMonthPayroll = $payrollMonth == "last_month" && $currentTime < $currentMonthPayrollTime
                        ? $dateFrom
                        : date("Y-m-01");
                    $where[] = "(DATE(r.created) >= " . Connection::GetSQLDate($dateFrom) . "
                        AND r.status != 'approve_proposed'
                        OR r.status = 'approve_proposed'
                        AND DATE(r.document_date) >= " . Connection::GetSQLDate($lastMonthPayroll) . ")";
                    break;
                default:
                    $where[] = "(DATE(r.created) >= " . Connection::GetSQLDate($dateFrom) . " OR r.status = 'approve_proposed')";
            }
        }

        if (!$dateTo) {
            $dateTo = date("Y-m-t", strtotime($currentDate));
        }

        $where[] = "DATE(r.created) <= " . Connection::GetSQLDate($dateTo);

        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.created, r.updated, CASE r.status WHEN 'supervisor' THEN 'review' ELSE r.status END AS status, r.document_guid,
						r.amount_approved, r.real_amount_approved, r.store_name, r.document_date, r.receipt_from, r.status_user_id, r.sets_of_goods,
						SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee, 
						(CASE WHEN SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) AS has_unread_comment_employee , r.booked, r.trip_id, cr.digit AS currency,
						r.datev_export, r.pdf_export, r.creditor_export_id
					FROM receipt AS r 
						LEFT JOIN receipt_comment AS c ON c.receipt_id=r.receipt_id 
						LEFT JOIN trip as tr ON r.trip_id=tr.trip_id
						LEFT JOIN currency AS cr ON r.currency_id=cr.currency_id
						LEFT JOIN UNNEST('{\"approve_proposed\",\"new\",\"review\",\"approved\",\"supervisor\",\"denied\"}'::text[]) WITH ORDINALITY AS t(status, ord) ON t.status=r.status"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " 
					GROUP BY r.receipt_id, t.ord, cr.digit";
        $this->SetOrderBy("api");
        $this->SetItemsOnPage(0);
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["can_delete"] = $this->_items[$i]["datev_export"] == 0 &&
                $this->_items[$i]["pdf_export"] == 0 &&
                $this->_items[$i]["creditor_export_id"] == null &&
                $this->_items[$i]["status"] != "denied";

            $receipt = new Receipt($this->module, $this->_items[$i]);
            if (!empty($this->_items[$i]["receipt_from"])) {
                $this->_items[$i]["receipt_type_title_translation"] = GetTranslation(
                    "receipt-type-" . $this->_items[$i]["receipt_from"],
                    "product"
                );
            } else {
                $this->_items[$i]["receipt_type_title_translation"] = "";
            }
            $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
            $this->_items[$i]["api_real_amount_approved"] = $specificProductGroup
                ? $specificProductGroup->GetApiRealAmountApproved($receipt)
                : 0;

            if (
                $receipt->GetIntProperty("group_id") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION) &&
                Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                    $receipt->GetIntProperty("employee_id"),
                    $receipt->GetProperty('created')
                ) == "N"
            ) {
                $employee = new Employee("company");
                $material_status = $employee->GetPropertyHistoryValueEmployee(
                    "material_status",
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("created")
                );

                if ($material_status["value"] == "single") {
                    $materialStatus = GetTranslation("material-status-single", "company");
                }
                if ($material_status["value"] == "married") {
                    $materialStatus = GetTranslation("material-status-married", "company");
                }

                $this->_items[$i]["material_status"] = $materialStatus;
                $child_count = $employee->GetPropertyHistoryValueEmployee(
                    "child_count",
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("created")
                );
                $this->_items[$i]["child_count"] = $child_count["value"];
            }

            if ($this->_items[$i]["currency"] != null) {
                continue;
            }

            $this->_items[$i]["currency"] = "EUR";
        }
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow($loadStatusUser = false)
    {
        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $query = "SELECT " . Connection::GetSQLDecryption("u.first_name") . " AS first_name, 
							" . Connection::GetSQLDecryption("u.last_name") . " AS last_name, 
							CONCAT(" . Connection::GetSQLDecryption("u.first_name") . ", ' ', " . Connection::GetSQLDecryption("last_name") . ") AS employee_name
						FROM employee AS e 
							JOIN user_info AS u ON u.user_id=e.user_id
						WHERE e.employee_id=" . intval($this->_items[$i]["employee_id"]);
            $row = $stmt->FetchRow($query);
            if (is_array($row)) {
                $this->_items[$i] = array_merge($this->_items[$i], $row);
            }

            $this->_items[$i]["status_title"] = GetTranslation(
                "receipt-status-" . $this->_items[$i]["status"],
                $this->module
            );
            $this->_items[$i]["created_seconds_ago"] = time() - strtotime($this->_items[$i]["created"]);
            $this->_items[$i]["is_processed"] = in_array(
                $this->_items[$i]["status"],
                array("approved", "approve_proposed", "denied")
            );
            if (isset($this->_items[$i]["group_code"])) {
                $this->_items[$i]["group_title_translation"] = GetTranslation(
                    "product-group-" . $this->_items[$i]["group_code"],
                    "product"
                );
            }

            $this->_items[$i]['approved_val'] = $this->_items[$i]['real_amount_approved']; //Dummy for future extension

            $statusUser = new User();
            $statusUser->LoadByID($this->_items[$i]["status_user_id"]);
            $this->_items[$i]["status_user_first_name"] = $statusUser->GetProperty("first_name");
            $this->_items[$i]["status_user_last_name"] = $statusUser->GetProperty("last_name");

            if (!isset($this->_items[$i]["group_code"])) {
                continue;
            }

            if ($this->_items[$i]["group_code"] == PRODUCT_GROUP__TRAVEL) {
                if ($this->_items[$i]["booked"] != 'Y') {
                    $this->_items[$i]["booked"] = 'N';
                }
            } else {
                $this->_items[$i]["booked"] = '';
            }
        }
    }

    /**
     * Outputs csv file with company unit's receipt based data formatted for Addison
     *
     * @param int $companyUnitID company_unit_id of company unit data to be exported of
     * @param string $payrollDate payroll date
     * @param int $payrollID payroll id
     * @param bool|int $operationID operation ID for cron
     *
     * @return bool
     */
    public function ExportToAddison($companyUnitID, $payrollDate, $payrollID, $operationID = false)
    {
        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start generating for Company Unit: " . $companyUnitID);
        }

        $payrollDate = $payrollDate ?: GetCurrentDate();

        //for Loga and Topas
        $payrollCurrentDate = $payrollDate;
        $partOfFileName = "-C";

        if (CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "last_month") {
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " -1 month"));
            $partOfFileName = "";
        }

        $payrollMonthObject = date_create($payrollDate);

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($companyUnitID);

        $lineList = array();

        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
            if ($operationID) {
                Operation::SaveCronStatus(
                    $operationID,
                    "DateV collect line info Product Group: " . $productGroupList->_items[$i]["code"] . " for Company Unit: " . $companyUnitID
                );
            }

            $specificProductGroup = SpecificProductGroupFactory::Create($productGroupList->_items[$i]["group_id"]);
            if ($specificProductGroup === null) {
                continue;
            }

            $lineList = array_merge($lineList, $specificProductGroup->GetAddisonExportLineList(
                $companyUnitID,
                $productGroupList->_items[$i]["group_id"],
                $payrollDate,
                "datev"
            ));
        }

        $processedReceipts = array();
        foreach (array_column($lineList, "receipt_ids") as $ids) {
            $processedReceipts = array_merge($processedReceipts, $ids);
        }
        $processedVouchers = array();
        foreach (array_column($lineList, "service_voucher_ids") as $ids) {
            $processedVouchers = array_merge($processedVouchers, $ids);
        }

        //sort generated line list
        array_multisort(
            array_column($lineList, "employee_id"),
            SORT_ASC,
            array_column($lineList, "group_id"),
            SORT_ASC,
            array_column($lineList, "month_key"),
            SORT_ASC,
            array_column($lineList, "amount"),
            SORT_DESC,
            $lineList
        );

        $formattedTaxConsultant = sprintf("%07d", $companyUnit->GetIntProperty("tax_consultant"));

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create lodas for Company Unit: " . $companyUnitID);
        }

        //create lodas file
        {
            $filename = "Lodas_" . $formattedTaxConsultant . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= "Dateiname: " . $filename . "\r\n";

            $content .= "\r\n";
            $content .= "[Allgemein]" . "\r\n";
            $content .= "Ziel=LODAS" . "\r\n";
            $content .= "BeraterNr=" . $companyUnit->GetProperty("tax_consultant") . "\r\n";
            $content .= "MandantenNr=" . $companyUnit->GetProperty("client_id") . "\r\n";

            $content .= "\r\n";
            $content .= "[Satzbeschreibung]" . "\r\n";
            $content .= "1;u_lod_bwd_buchung_standard;abrechnung_zeitraum#bwd;bs_wert_butab#bwd;bs_nr#bwd;la_eigene#bwd;pnr#bwd;" . "\r\n";

            $content .= "\r\n";
            $content .= "[Bewegungsdaten]" . "\r\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                1,
                date("01.m.Y", strtotime($line["month_key"] . "01")),//$dateToObject->format("01/m/Y"),
                str_replace(".", ",", $line["amount"]),
                "2",
                trim($line["acc"], "_"),
                $line["employee_guid"]
            );
            $content .= implode(";", $outputLine) . ";" . "\r\n";
        }

            $content = mb_convert_encoding($content, "windows-1252", "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("lodas_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create lug for Company Unit: " . $companyUnitID);
        }
        //create lug file
        {
            $filename = "Lug_" . $formattedTaxConsultant . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= $formattedTaxConsultant . ";" . $companyUnit->GetProperty("client_id") . ";" . $payrollMonthObject->format("m/Y") . ";\r\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                $line["employee_guid"],
                trim($line["acc"], "_"),
                str_replace(".", ",", $line["amount"]),
                $line["cost_center_number"],
                ""
            );
            $content .= implode(";", $outputLine) . "\r\n";
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("lug_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create LOGGA for Company Unit: " . $companyUnitID);
        }
        //create LOGGA file
        {
            $filename = date(
                "Y-m",
                strtotime($payrollCurrentDate)
            ) . "-" . $companyUnit->GetProperty("customer_guid") . "-LOGGA" . $partOfFileName . ".csv";

            $loggaTableHeader = explode(
                ";",
                "Satzart; Funktion; Mandant; Abrechnungskreis; Mitarbeiternr.; ; ; ; Lohnart; Tagesfaktor; Menge; Basis; Betrag; Kostenstelle; Kostenart; Kostenträger; Datum; Zuordnungsmonat; Niederlassung; ;"
            );

            $loggaTableBody = array();
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $row = array(
                "[VARTAB]",
                //always [VARTAB]
                "INSERT",
                //always INSERT
                $companyUnit->GetProperty("tax_consultant"),
                //Tax Consultant ID
                $companyUnit->GetProperty("client_id"),
                //client (customer) number
                trim($line["employee_guid"]),
                //Employee ID
                "",
                "",
                "",
                //empty
                trim($line["acc"], "_"),
                //ACC field of the service
                "",
                "",
                "",
                //empty
                str_replace(".", ",", GetPriceFormat($line["amount"])),
                //Result of all the approved receipts for a service
                $line["cost_center_number"],
                //Cost Center Number of an employee
                "",
                "",
                //empty
                date("Y-m-01", strtotime($line["month_key"] . "01")),
                date("Y-m-01", strtotime($line["month_key"] . "01"))
                //date of export
            );
            $loggaTableBody[] = $row;
        }

            //create spreadsheet and write the data
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($loggaTableHeader, null, "A1");
            $spreadsheet->getActiveSheet()->fromArray($loggaTableBody, null, "A2");

            //save file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(";");
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $tempFilePath = PROJECT_DIR . "var/log/export_datev_logga_" . date("U") . "_" . rand(100, 999) . ".csv";
            $writer->setUseBOM(true);
            $writer->save($tempFilePath);

            $content = file_get_contents($tempFilePath);
            $content = str_replace("\"", "", $content);
            $content = mb_convert_encoding($content, CompanyUnit::GetPropertyValue("datev_encoding", $companyUnitID));
            $f = fopen($tempFilePath, "w");
            fwrite($f, $content);
            fclose($f);

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->MoveToStorage($tempFilePath, PAYROLL_DIR, $filename);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("logga_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create Topas for Company Unit: " . $companyUnitID);
        }
        //create Topas file
        {
            $filename = date(
                "Y-m",
                strtotime($payrollCurrentDate)
            ) . "-" . $companyUnit->GetProperty("customer_guid") . "-topas" . $partOfFileName . ".csv";

            //there's no header in file itself, but adding it for reference anyway
            $topasTableHeader = explode(
                ";",
                "FIRMA; PERSN; JJMM; JJMMV; LOA; BEZZT; Tage; FAKTOR; LOBET_F; WKZ; LOBET; LOBEST; LOBESV; KOSTL; KOTR; VORG; BEDT; KZRAB; HERKZ; LKZ; ERFUSR; ERFDT; CHGUSR; CHGDT; CHGZT"
            );

            $topasTableBody = array();
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $row = array(
                $companyUnit->GetProperty("client_id"),
                //client id
                $line["employee_guid"],
                //employee ID
                "",
                //always empty
                $payroll->GetProperty("payroll_month"),
                //date("Ym", strtotime($payrollDate)), //payroll month
                trim($line["acc"], "_"),
                //ACC field of the service
                "",
                "",
                "",
                "",
                "",
                //always empty
                str_replace(",", "", GetPriceFormat($line["amount"])),
                //result of all the approved receipts for a service
                "",
                "",
                //always empty
                $line["cost_center_number"],
                //Cost Center Number of an employee
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                ";"
            );
            $topasTableBody[] = $row;
        }

        if (count($topasTableBody) == 0) {
            $topasTableBody[] = array(";;;;;;;;;;;;;;;;;;;;;;;;;");
        }

            //create spreadsheet and write the data
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($topasTableBody, null, "A1");

            //save file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(";");
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $tempFilePath = PROJECT_DIR . "var/log/export_datev_topas_" . date("U") . "_" . rand(100, 999) . ".csv";
            $writer->save($tempFilePath);

            $content = file_get_contents($tempFilePath);
            $content = str_replace("\"", "", $content);
            $content = mb_convert_encoding($content, "CP1252", "utf-8");

            $f = fopen($tempFilePath, "w");
            fwrite($f, $content);
            fclose($f);

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->MoveToStorage($tempFilePath, PAYROLL_DIR, $filename);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("topas_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create addison for Company Unit: " . $companyUnitID);
        }
        //create addison file
        {
            $filename = "Addison_" . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= "Firma;Personalnummer;Lohnart;Kostenstelle;Kostenträger;Abrechnungstag;Abrechnungszeitraum;Lohnsatz;Prozentsatz;Anzahl Tage;Anzahl Stunden;Betrag\n";
            $content .= "\r\n";
            $content .= "Daten Lohnimport:\r\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                trim($companyUnit->GetProperty("client_id")) . ";",
                trim($line["employee_guid"]) . ";",
                trim($line["acc"], "_") . ";",
                trim($line["cost_center_number"]) . ";",
                ";;",
                $payrollMonthObject->format("00mY"),
                ";;;;;",
                str_replace(".", ",", GetPriceFormat($line["amount"])) . ";\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("addison_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create lexware for Company Unit: " . $companyUnitID);
        }
        //create lexware file
        {
            $filename = "Lexware" . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= "Jahr;Monat;Personalnummer;Lohnartnummer;Wert\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                $payrollMonthObject->format("Y") . ";",
                $payrollMonthObject->format("m") . ";",
                $line["employee_guid"] . ";",
                trim($line["acc"], "_") . ";",
                str_replace(".", ",", GetPriceFormat($line["amount"])) . "\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("lexware_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create perforce for Company Unit: " . $companyUnitID);
        }
        //create perforce file
        {
            $filename = $payrollMonthObject->format("Y-m") . "-" . $companyUnit->GetProperty("customer_guid") . "-" . "Perforce" . ".csv";

            $content = "";
            $content .= "AGNR;PNR;LA;JJJJMM;Betrag;Kostenstelle\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                (strlen($companyUnit->GetProperty("client_id")) > 0 ? str_pad(
                    $companyUnit->GetProperty("client_id"),
                    5,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //client id
                (strlen($line["employee_guid"]) > 0 ? str_pad(
                    $line["employee_guid"],
                    6,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //employee ID
                (strlen(trim($line["acc"], "_")) > 0 ? str_pad(
                    trim($line["acc"], "_"),
                    4,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //acc
                $payrollMonthObject->format("Ym") . ";", //JJJJMM
                str_replace(".", ",", GetPriceFormat($line["amount"])) . ";",
                trim($line["cost_center_number"]) . "\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("perforce_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create addison for Company Unit: " . $companyUnitID);
        }
        //create addison file
        {
            $filename = "Addison_" . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= "Firma;Personalnummer;Lohnart;Kostenstelle;Kostenträger;Abrechnungstag;Abrechnungszeitraum;Lohnsatz;Prozentsatz;Anzahl Tage;Anzahl Stunden;Betrag\n";
            $content .= "\r\n";
            $content .= "Daten Lohnimport:\r\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                trim($companyUnit->GetProperty("client_id")) . ";",
                trim($line["employee_guid"]) . ";",
                trim($line["acc"], "_") . ";",
                trim($line["cost_center_number"]) . ";",
                ";;",
                $payrollMonthObject->format("00mY"),
                ";;;;;",
                str_replace(".", ",", GetPriceFormat($line["amount"])) . ";\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("addison_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create lexware for Company Unit: " . $companyUnitID);
        }
        //create lexware file
        {
            $filename = "Lexware" . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".txt";

            $content = "";
            $content .= "Jahr;Monat;Personalnummer;Lohnartnummer;Wert\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                $payrollMonthObject->format("Y") . ";",
                $payrollMonthObject->format("m") . ";",
                $line["employee_guid"] . ";",
                trim($line["acc"], "_") . ";",
                str_replace(".", ",", GetPriceFormat($line["amount"])) . "\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("lexware_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create perforce for Company Unit: " . $companyUnitID);
        }
        //create perforce file
        {
            $filename = $payrollMonthObject->format("Y-m") . "-" . $companyUnit->GetProperty("customer_guid") . "-" . "Perforce" . ".csv";

            $content = "";
            $content .= "AGNR;PNR;LA;JJJJMM;Betrag;Kostenstelle\n";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                (strlen($companyUnit->GetProperty("client_id")) > 0 ? str_pad(
                    $companyUnit->GetProperty("client_id"),
                    5,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //client id
                (strlen($line["employee_guid"]) > 0 ? str_pad(
                    $line["employee_guid"],
                    6,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //employee ID
                (strlen(trim($line["acc"], "_")) > 0 ? str_pad(
                    trim($line["acc"], "_"),
                    4,
                    '0',
                    STR_PAD_LEFT
                ) : "") . ";", //acc
                $payrollMonthObject->format("Ym") . ";", //JJJJMM
                str_replace(".", ",", GetPriceFormat($line["amount"])) . ";",
                trim($line["cost_center_number"]) . "\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("perforce_file", $filename);
        }

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "DateV start create SAGE for Company Unit: " . $companyUnitID);
        }
        //create SAGE file
        {
            $filename = "SAGE_" . $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Ym") . ".txt";

            $content = "";

            //transform generated line list to output format
        foreach ($lineList as $line) {
            if ($line["amount"] == 0) {
                continue;
            }

            $outputLine = array(
                trim($companyUnit->GetProperty("client_id")) . ";", //client id
                $payrollMonthObject->format("n") . ";", //number of month, m
                $payrollMonthObject->format("Y") . ";", //year, JJJJ
                trim($line["employee_guid"]) . ";",
                trim($line["acc"], "_") . ";",
                ";;;;;",
                str_replace(".", ",", GetPriceFormat($line["amount"])) . ";\r\n"
            );
            $content .= implode("", $outputLine);
        }

            $content = mb_convert_encoding($content, "utf-8");

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->PutFileContent(PAYROLL_DIR . $filename, $content);

            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $payroll->UpdateField("sage_file", $filename);
        }

        if ($processedReceipts) {
            $stmt = GetStatement(DB_MAIN);
            $flag = $payrollMonthObject->format("Ym");
            $query = "UPDATE receipt SET datev_export=" . Connection::GetSQLString($flag) . " WHERE receipt_id IN(" . implode(
                ",",
                $processedReceipts
            ) . ")";
            $stmt->Execute($query);
        }
        if ($processedVouchers) {
            $stmt = GetStatement(DB_MAIN);
            $flag = $payrollMonthObject->format("Ym");
            $query = "UPDATE voucher SET datev_export=" . Connection::GetSQLString($flag) . " WHERE voucher_id IN(" . implode(
                ",",
                $processedVouchers
            ) . ")";
            $stmt->Execute($query);
        }

        return true;
    }

    /**
     * Loads receipt for Addison export
     *
     * @param int $companyUnitID
     * @param int $groupID
     * @param string $payrollDate
     * @param string $exportType
     */
    public function LoadReceiptListForAddison($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $checkBooked = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL) == $groupID;

        if (CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "last_month" || $exportType == "reset") {
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " +1 month"));
            $statusUpdatedTo = date("Y-m-d 17:59:00", strtotime($payrollDate));
        } else {
            $statusUpdatedTo = date("Y-m-d 17:59:00", strtotime($payrollDate));
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " +1 month"));
        }

        $monthBegin = $exportType != "voucher" ? date_create($payrollDate)->format("Y-m-01") : $payrollDate;
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        $voucherServices = array(
            array(
                "product_group" => PRODUCT_GROUP__BENEFIT_VOUCHER,
                "payroll_export_option" => OPTION__BENEFIT_VOUCHER__MAIN__PAYROLL_EXPORT
            ),
            array(
                "product_group" => PRODUCT_GROUP__FOOD_VOUCHER,
                "payroll_export_option" => OPTION__FOOD_VOUCHER__MAIN__PAYROLL_EXPORT
            ),
            array(
                "product_group" => PRODUCT_GROUP__GIFT_VOUCHER,
                "payroll_export_option" => OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT
            )
        );

        if (
            $key = array_search(
                ProductGroup::GetProductGroupCodeByID($groupID),
                array_column($voucherServices, "product_group")
            ) && $exportType == "datev"
        ) {
            $tmpEmployeeIDs = $employeeIDs;
            $employeeIDs = array();
            foreach ($tmpEmployeeIDs as $employeeID) {
                if (
                    Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $voucherServices[$key]["payroll_export_option"],
                        $employeeID,
                        $payrollDate
                    ) != "Y"
                ) {
                    continue;
                }

                $employeeIDs[] = $employeeID;
            }
        }
        if (count($employeeIDs) <= 0) {
            return;
        }

        $where = array();
        $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        $where[] = "r.group_id=" . intval($groupID);
        if ($exportType != "reset") {
            $where[] = "DATE(r.status_updated) <= " . Connection::GetSQLDateTime($statusUpdatedTo);
        }
        $where[] = "DATE(r.document_date) < " . Connection::GetSQLDate($monthBegin);
        $where[] = "r.status='approved'";
        $where[] = "r.archive='N'";
        $where[] = "(r.receipt_from!='doc' OR r.receipt_from IS NULL)";
        if ($checkBooked) {
            $where[] = "r.booked='Y'";
        }
        if ($exportType == "pdf") {
            $where[] = "r.pdf_export='0'";
        } elseif ($exportType == "datev" || $exportType == "voucher") {
            $where[] = "r.datev_export='0'";
        } elseif ($exportType == "reset") {
            $flag = date("Ym", strtotime($payrollDate . " -1 month"));
            $where[] = "(r.datev_export=" . Connection::GetSQLString($flag) . " OR r.pdf_export=" . Connection::GetSQLString($flag) . ")";
        }

        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.amount_approved, r.real_amount_approved, DATE(r.document_date) as document_date,
                r.datev_export, r.pdf_export, r.trip_id, r.days_amount_under_16, r.days_amount_over_16, r.receipt_from
                FROM receipt AS r "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->SetOrderBy("document_date_asc");
        $this->SetItemsOnPage(0);
        $this->LoadFromSQL($query);
    }

    /**
     * Creates file with company unit's receipt based data formatted for internal purposes
     *
     * @param int $companyUnitID company_unit_id of company unit data to be exported of
     * @param string $payrollDate
     * @param int $payrollID
     * @param bool $payrollTest if true, return array of lines that would go into PDF
     *
     * @return array|int|bool $payroll_id id of new payroll or false on empty payroll.
     */
    function ExportForInternalPurposes(
        $companyUnitID,
        $payrollDate,
        $payrollID,
        $operationID = false,
        $payrollTest = false
    ) {
        if ($operationID) {
            Operation::SaveCronStatus($operationID, "PDF start generating for Company Unit: " . $companyUnitID);
        }

        $payrollDate = $payrollDate ?: GetCurrentDate();
        if (CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "last_month") {
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " -1 month"));
        }
        $payrollMonthObject = date_create($payrollDate);

        $processedReceipts = array();
        $processedVouchers = array();

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($companyUnitID);

        $productGroupList = new ProductGroupList("billing");
        $productGroupList->LoadProductGroupListForAdmin();

        $exportProductGroupList = array();
        foreach ($productGroupList->GetItems() as $productGroup) {
            if ($productGroup["receipts"] != "Y") {
                continue;
            }

            $exportProductGroupList[] = $productGroup;
        }

        //build new structure based on addison line list
        $lineList = array();
        for ($i = 0; $i < count($exportProductGroupList); $i++) {
            if ($operationID) {
                Operation::SaveCronStatus(
                    $operationID,
                    "PDF collect line info Product Group: " . $exportProductGroupList[$i]["code"] . " for Company Unit: " . $companyUnitID
                );
            }

            $specificProductGroup = SpecificProductGroupFactory::Create($exportProductGroupList[$i]["group_id"]);
            if ($specificProductGroup === null) {
                continue;
            }

            $lineList = array_merge($lineList, $specificProductGroup->GetAddisonExportLineList(
                $companyUnitID,
                $exportProductGroupList[$i]["group_id"],
                $payrollDate,
                "pdf"
            ));
        }

        $exportProductGroupList = array_combine(
            array_column($exportProductGroupList, "group_id"),
            $exportProductGroupList
        );

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "PDF build list for Company Unit: " . $companyUnitID);
        }
        //build employee list and process each employee
        $employeeList = array();
        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        foreach ($employeeIDs as $employeeID) {
            $employee = new Employee("company");
            if (!$employee->LoadByID($employeeID)) {
                continue;
            }

            $employeeProductGroupList = $exportProductGroupList;

            $employeeKeys = array_keys(array_column($lineList, "employee_id"), $employee->GetProperty("employee_id"));
            foreach ($employeeKeys as $employeeKeysKey => $employeeKey) {
                $line = $lineList[$employeeKey];
                if ($line["line_key"] == "negative_line") {
                    continue;
                }

                if (isset($employeeProductGroupList[$line['group_id']][$line["line_key"]])) {
                    $employeeProductGroupList[$line['group_id']][$line["line_key"]] += $line['amount'];
                    $employeeProductGroupList[$line['group_id']]['receipt_ids'] = array_merge(
                        $employeeProductGroupList[$line['group_id']]['receipt_ids'],
                        $line['receipt_ids']
                    );
                    $employeeProductGroupList[$line['group_id']]['legal_receipt_ids'] = array_merge(
                        $employeeProductGroupList[$line['group_id']]['legal_receipt_ids'],
                        $line['legal_receipt_ids']
                    );
                    if (isset($line['voucher_ids'])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['voucher_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array();
                        }
                        $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array_merge(
                            $employeeProductGroupList[$line['group_id']]['voucher_ids'],
                            $line['voucher_ids']
                        );
                    }
                    if (isset($line["trip_ids"])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['trip_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['trip_ids'] = $line['trip_ids'];
                        } else {
                            $employeeProductGroupList[$line['group_id']]['trip_ids'] = array_merge(
                                $employeeProductGroupList[$line['group_id']]['trip_ids'],
                                $line['trip_ids']
                            );
                        }
                    }
                    if (isset($line['service_voucher_ids'])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['service_voucher_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['service_voucher_ids'] = array();
                        }
                        $employeeProductGroupList[$line['group_id']]['service_voucher_ids'] = array_merge(
                            $employeeProductGroupList[$line['group_id']]['service_voucher_ids'],
                            $line['service_voucher_ids']
                        );
                    }
                } else {
                    $employeeProductGroupList[$line['group_id']][$line["line_key"]] = $line['amount'];
                    if (isset($line['receipt_ids'])) {
                        $employeeProductGroupList[$line['group_id']]['receipt_ids'] = $line['receipt_ids'];
                    }
                    if (isset($line['legal_receipt_ids'])) {
                        $employeeProductGroupList[$line['group_id']]['legal_receipt_ids'] = $line['legal_receipt_ids'];
                    }
                    if (isset($line['voucher_ids'])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['voucher_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array();
                        }
                        $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array_merge(
                            $employeeProductGroupList[$line['group_id']]['voucher_ids'],
                            $line['voucher_ids']
                        );
                    }
                    if (isset($line["trip_ids"])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['trip_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['trip_ids'] = $line['trip_ids'];
                        } else {
                            $employeeProductGroupList[$line['group_id']]['trip_ids'] = array_merge(
                                $employeeProductGroupList[$line['group_id']]['trip_ids'],
                                $line['trip_ids']
                            );
                        }
                    }
                    if (isset($line['service_voucher_ids'])) {
                        if (!isset($employeeProductGroupList[$line['group_id']]['service_voucher_ids'])) {
                            $employeeProductGroupList[$line['group_id']]['service_voucher_ids'] = array();
                        }
                        $employeeProductGroupList[$line['group_id']]['service_voucher_ids'] = array_merge(
                            $employeeProductGroupList[$line['group_id']]['service_voucher_ids'],
                            $line['service_voucher_ids']
                        );
                    }
                }
            }
            /*
                        //find the lines for current employee+product group and group them by line_key
                        foreach($lineList as $lineKey => $line)
                        {
                            if($line["employee_id"] == $employee->GetProperty("employee_id") && $line["line_key"] != "negative_line")
                            {
                                if (isset($employeeProductGroupList[$line['group_id']][$line["line_key"]])){
                                    $employeeProductGroupList[$line['group_id']][$line["line_key"]]+= $line['amount'];
                                    $employeeProductGroupList[$line['group_id']]['receipt_ids'] = array_merge($employeeProductGroupList[$line['group_id']]['receipt_ids'], $line['receipt_ids']);
                                    $employeeProductGroupList[$line['group_id']]['legal_receipt_ids'] = array_merge($employeeProductGroupList[$line['group_id']]['legal_receipt_ids'], $line['legal_receipt_ids']);
                                    if(isset($line['voucher_ids']))
                                    {
                                        if(!isset($employeeProductGroupList[$line['group_id']]['voucher_ids']))
                                            $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array();
                                        $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array_merge($employeeProductGroupList[$line['group_id']]['voucher_ids'], $line['voucher_ids']);
                                    }

                                }
                                else{
                                    $employeeProductGroupList[$line['group_id']][$line["line_key"]] = $line['amount'];
                                    $employeeProductGroupList[$line['group_id']]['receipt_ids'] = $line['receipt_ids'];
                                    $employeeProductGroupList[$line['group_id']]['legal_receipt_ids'] = $line['legal_receipt_ids'];
                                    if(isset($line['voucher_ids']))
                                    {
                                        if(!isset($employeeProductGroupList[$line['group_id']]['voucher_ids']))
                                            $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array();
                                        $employeeProductGroupList[$line['group_id']]['voucher_ids'] = array_merge($employeeProductGroupList[$line['group_id']]['voucher_ids'], $line['voucher_ids']);
                                    }
                                }
                            }
                        }
            */

            //Group data for result view
            foreach ($employeeProductGroupList as $group_id => $product) {
                if (!isset($product['receipt_ids']) && !isset($product["service_voucher_ids"])) {
                    unset($employeeProductGroupList[$group_id]);
                    continue;
                }

                if (
                    in_array(
                        $product['code'],
                        [PRODUCT_GROUP__INTERNET, PRODUCT_GROUP__RECREATION, PRODUCT_GROUP__BONUS]
                    )
                ) {
                    $employeeProductGroupList[$group_id]['tax_flat'] = $product['positive_line'];
                } elseif (!in_array($product['code'], [PRODUCT_GROUP__FOOD, PRODUCT_GROUP__FOOD_VOUCHER])) {
                    $employeeProductGroupList[$group_id]['tax_free'] = $product['positive_line'];
                }

                $receiptList = array();

                if ($group_id == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)) {
                    $employeeProductGroupList[$group_id]['receipt_count'] = 0;
                    $employeeProductGroupList[$group_id]['receipt_ids'] = array();
                    $employeeProductGroupList[$group_id]["trip_ids"] = array();
                    foreach ($product["trip_ids"] as $trip) {
                        $tripKey = array_search(
                            $trip["trip_id"],
                            array_column($employeeProductGroupList[$group_id]["trip_ids"], "trip_id")
                        );
                        $receiptIDs = explode(", ", $trip["receipt_ids"]);
                        $legalReceiptIDs = explode(", ", $trip["legal_receipt_ids"]);
                        if ($tripKey === false) {
                            $employeeProductGroupList[$group_id]["trip_ids"][] = $trip;
                            $tripKey = count($employeeProductGroupList[$group_id]["trip_ids"]) - 1;
                            $employeeProductGroupList[$group_id]["trip_ids"][$tripKey]["receipt_ids"] = array();
                            $employeeProductGroupList[$group_id]["trip_ids"][$tripKey]["legal_receipt_ids"] = array();
                        }

                        //made in this way to avoid problems with content
                        foreach ($receiptIDs as $key => $receiptID) {
                            $employeeProductGroupList[$group_id]['receipt_ids'][] = array("receipt_id" => $receiptID);
                            $employeeProductGroupList[$group_id]["trip_ids"][$tripKey]["receipt_ids"][] = array(
                                "receipt_id" => $receiptID,
                                "legal_receipt_id" => $legalReceiptIDs[$key]
                            );
                        }

                        if (count($receiptIDs) > 0) {
                            $processedReceipts = array_merge($processedReceipts, $receiptIDs);
                        }

                        $employeeProductGroupList[$group_id]['receipt_count'] += count($receiptIDs);
                    }
                } elseif (isset($product['receipt_ids']) && count($product['receipt_ids']) > 0) {
                    $processedReceipts = array_merge($processedReceipts, $product['receipt_ids']);
                    foreach ((array)$product["receipt_ids"] as $receiptKey => $receiptID) {
                        $receiptList[$receiptID] = array(
                            "receipt_id" => $receiptID,
                            "legal_receipt_id" => (isset($product["voucher_ids"]) ? $product["legal_receipt_ids"][$receiptKey] . $product["voucher_ids"][$receiptKey] : $product["legal_receipt_ids"][$receiptKey])
                        );
                    }
                }

                $serviceVoucherList = array();
                if (isset($product["service_voucher_ids"])) {
                    foreach ($product["service_voucher_ids"] as $voucherKey => $voucherID) {
                        $serviceVoucherList[$voucherID] = array("voucher_id" => $voucherID);
                    }
                    $processedVouchers = array_merge($processedVouchers, $product['service_voucher_ids']);
                }

                if ($group_id != ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)) {
                    $employeeProductGroupList[$group_id]['receipt_ids'] = array_values($receiptList);
                    $employeeProductGroupList[$group_id]["receipt_count"] = count($receiptList);
                }
                if (count($serviceVoucherList) > 0) {
                    $employeeProductGroupList[$group_id]['service_voucher_ids'] = array_values($serviceVoucherList);
                    $employeeProductGroupList[$group_id]['service_voucher_count'] = count($serviceVoucherList);
                }
                unset($employeeProductGroupList[$group_id]["legal_receipt_ids"]);
                unset($employeeProductGroupList[$group_id]["voucher_ids"]);
            }

            if (count($employeeProductGroupList) <= 0) {
                continue;
            }

            $employeeList[] = array(
                "last_name" => $employee->GetProperty("last_name"),
                "first_name" => $employee->GetProperty("first_name"),
                "birthday" => $employee->GetProperty("birthday"),
                "employee_guid" => $employee->GetProperty("employee_guid"),
                "product_group_list" => $employeeProductGroupList
            );
        }

        //sort generated employee list
        array_multisort(
            array_column($employeeList, "last_name"),
            SORT_ASC,
            array_column($employeeList, "first_name"),
            SORT_ASC,
            $employeeList
        );

        if ($operationID) {
            Operation::SaveCronStatus($operationID, "PDF start pdf generate for Company Unit: " . $companyUnitID);
        }

        if ($payrollTest) {
            return $employeeList;
        }

        $popupPage = new PopupPage($this->module);
        $content = $popupPage->Load("receipt_list_internal_pdf.html");
        $content->SetLoop("EmployeeList", $employeeList);
        $content->SetLoop("ExportProductGroupList", $exportProductGroupList);
        $exportTimestamp = $payrollMonthObject->getTimestamp();
        $monthYear = GetGermanMonthName(date("m", $exportTimestamp)) . " " . date("Y", $exportTimestamp);
        $content->SetVar("export_month_year", $monthYear);
        $content->SetVar("product_group_column_width", 60 / count($exportProductGroupList));

        $month = date("m");
        $deMonth = GetGermanMonthName($month);
        $dayMonthYear = strftime("%d " . $deMonth . " %Y / %H:%M Uhr", time());
        $content->SetVar("export_day_month_year", $dayMonthYear);

        foreach ($companyUnit->GetProperties() as $key => $value) {
            $content->SetVar("COMPANY_UNIT_" . $key, $value);
        }

        $fileName = $companyUnit->GetProperty("customer_guid") . "_" . $payrollMonthObject->format("Y_m") . ".pdf";
        $content->SetVar("file_name", $fileName);

        $html = $popupPage->Grab($content);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 11,
            'default_font' => 'dejavusans',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 16,
            'margin_header' => 4,
            'margin_footer' => 6,
            'tempDir' => PAYROLL_TMP_DIR,
            'orientation' => 'P',
        ]);
        $pdf->PDFA = true;
        $pdf->PDFAauto = true;
        $css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/receipt_list_internal_pdf_style.css");
        $pdf->simpleTables = true;
        $pdf->packTableData = true;
        //this was needed for PDF/A-3 generation
        //$pdf->pdf_version = "1.7";

        $pdf->WriteHTML($css, 1);
        if ($operationID) {
            Operation::SaveCronStatus($operationID, "PDF write html for Company Unit: " . $companyUnitID);
        }
        $pdf->WriteHTML($html, 2);

        $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);

        $pdfPath = PROJECT_DIR . "var/log/" . $fileName;
        $pdf->Output($pdfPath, "F");

        $fileStorage->MoveToStorage($pdfPath, PAYROLL_DIR, $fileName);

        if ($processedReceipts) {
            $stmt = GetStatement(DB_MAIN);
            $flag = $payrollMonthObject->format("Ym");
            $query = "UPDATE receipt SET pdf_export=" . Connection::GetSQLString($flag) . " WHERE receipt_id IN(" . implode(
                ",",
                $processedReceipts
            ) . ")";
            $stmt->Execute($query);
        }
        if ($processedVouchers) {
            $stmt = GetStatement(DB_MAIN);
            $flag = $payrollMonthObject->format("Ym");
            $query = "UPDATE voucher SET pdf_export=" . Connection::GetSQLString($flag) . " WHERE voucher_id IN(" . implode(
                ",",
                $processedVouchers
            ) . ")";
            $stmt->Execute($query);
        }

        $payroll = new Payroll("billing");
        $payroll->LoadByID($payrollID);

        return $payroll->UpdateField("pdf_file", $fileName);
    }

    /**
     * Returns sum of real_amount_approved of filtered receipts with status approved/approve_proposed
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $statusList
     * @param ?string $voucherExport - filter by voucher export. different logic for "monthly" and "yearly"
     * @param bool $filterByStatusDate - use status_updated for filter instead of document_date
     *
     * @return number|array
     */
    public static function GetRealApprovedAmount(
        $employeeID,
        $groupID,
        $exceptReceiptID,
        $dateFrom = null,
        $dateTo = null,
        $statusList = ["approved", "approve_proposed"],
        $voucherExport = null,
        $filterByStatusDate = false
    ) {
        $currentDate = date_create();

        $includeOnlyCurrentMonthExportReceipts = date("m", strtotime($dateFrom)) == $currentDate->format("m")
            && $voucherExport != "yearly";

        $stmt = GetStatement();
        $where = array();
        if ($employeeID > 0) {
            $where[] = "employee_id=" . intval($employeeID);
        }
        if ($groupID > 0) {
            $where[] = "group_id=" . intval($groupID);
        }
        if ($exceptReceiptID > 0) {
            $where[] = "receipt_id!=" . intval($exceptReceiptID);
        }
        if ($voucherExport != null && !$filterByStatusDate && !$includeOnlyCurrentMonthExportReceipts) {
            if ($dateFrom != null) {
                $where[] = " DATE(ve.created) >= " . Connection::GetSQLDate($dateFrom);
            }
            if ($dateTo != null) {
                $where[] = " DATE(ve.created) <= " . Connection::GetSQLDate($dateTo);
            }
        } else {
            if ($dateFrom != null) {
                $where[] = $filterByStatusDate
                    ? "DATE(status_updated) >= " . Connection::GetSQLDate($dateFrom)
                    : "DATE(document_date) >= " . Connection::GetSQLDate($dateFrom);
            }
            if ($dateTo != null) {
                $where[] = $filterByStatusDate
                    ? "DATE(status_updated) <= " . Connection::GetSQLDate($dateTo)
                    : "DATE(document_date) <= " . Connection::GetSQLDate($dateTo);
            }
        }

        $where[] = "status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")";
        $where[] = "archive='N'";

        if ($voucherExport == null) {
            $query = "SELECT SUM(real_amount_approved)
					FROM receipt r"
                . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");

            return floatval($stmt->FetchField($query));
        }

        //if it's voucher service, we need to calculate approved unit count
        $query = "SELECT r.receipt_id, r.status, r.created, r.creditor_export_id,
                        r.real_amount_approved, r.document_date, ve.export_month
                    FROM receipt r
                    LEFT JOIN voucher_export_datev ve ON ve.export_id=r.creditor_export_id"
            . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $receiptList = $stmt->FetchList($query);
        $receiptList = ReceiptList::GetLinksForReceiptList($receiptList);

        $amountApproved = $countApproved = 0;
        foreach ($receiptList as $receipt) {
            if (!isset($receipt["link_list"])) {
                continue;
            }

            if (
                $includeOnlyCurrentMonthExportReceipts &&
                $receipt["creditor_export_id"] &&
                $receipt["export_month"] <> $currentDate->format("Ym")
            ) {
                continue;
            }

            foreach ($receipt["link_list"] as $link) {
                $countApproved += $link["link_amount"] / $link["voucher_amount"];
                $amountApproved += $link["link_amount"];
            }
        }

        return ["amount" => $amountApproved, "count" => number_format($countApproved, 10, ".", "")];
    }

    /**
     * Returns count of receipts with status approved/approve_proposed
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return number
     */
    public static function GetApprovedReceiptCount($employeeID, $groupID, $exceptReceiptID, $dateFrom, $dateTo)
    {
        $stmt = GetStatement();
        $where = array();
        if ($employeeID > 0) {
            $where[] = "employee_id=" . intval($employeeID);
        }
        if ($groupID > 0) {
            $where[] = "group_id=" . intval($groupID);
        }
        if ($exceptReceiptID > 0) {
            $where[] = "receipt_id!=" . intval($exceptReceiptID);
        }
        $where[] = "DATE(document_date) >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($dateTo);
        $where[] = "status IN('approved', 'approve_proposed')";
        $where[] = "archive='N'";
        $where[] = "(receipt_from!='doc' OR receipt_from IS NULL)";

        $query = "SELECT COUNT(*) FROM receipt "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        return intval($stmt->FetchField($query));
    }

    public static function GetApproveProposedReceiptIDsForProcessedNotification($date)
    {
        $stmt = GetStatement();

        $where = array();
        $days = abs(intval(Config::GetConfigValue("push_receipt_processed_remind_after_days")));
        $where[] = "DATE(status_updated + INTERVAL '" . $days . " days')=" . Connection::GetSQLDate($date);
        $where[] = "status='approve_proposed'";
        $where[] = "archive='N'";

        $query = "SELECT receipt_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $receiptList = $stmt->FetchList($query);

        return array_column($receiptList, "receipt_id");
    }

    public static function GetApproveProposedReceiptIDsForPayrollNotification($date)
    {
        $beforeDays = abs(intval(Config::GetConfigValue("push_receipt_payroll_before_days")));

        $dateObject = new DateTime($date);
        $dateObject->modify("+" . $beforeDays . " days");

        $payrollDay = $dateObject->format("j");

        $employeeIDs = array();
        $companyUnitIDs = CompanyUnitList::GetRootCompanyUnitIDsByPayrollDay($payrollDay);
        foreach ($companyUnitIDs as $companyUnitID) {
            $employeeIDs = array_merge(
                $employeeIDs,
                EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID)
            );
        }

        if (count($employeeIDs) > 0) {
            $stmt = GetStatement();

            $where = array();
            $where[] = "status='approve_proposed'";
            $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
            $where[] = "archive='N'";

            $query = "SELECT receipt_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
                " AND ",
                $where
            ) : "");
            $receiptList = $stmt->FetchList($query);

            return array_column($receiptList, "receipt_id");
        }

        return array();
    }

    public static function GetReceiptIDsForExpiringBenefitNotification($date, $type)
    {
        $receiptIDs = array();

        $employeeIDs = EmployeeList::GetAllEmployeeIDs();
        $employeeIDs = array_filter($employeeIDs, static function ($employeeID) use ($date) {
            $receiptOption = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
                $employeeID,
                $date
            );

            return $receiptOption == "yearly";
        });

        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT);

        if (count($employeeIDs) > 0) {
            $stmt = GetStatement();

            if ($type == 1) {
                /**
                 * first day of last month of receipt's period.
                 * example: document_date=2018-06-10, this notification should be sent 2019-05-11
                 */
                $where = array();
                $where[] = "status='approved'";
                $where[] = "archive='N'";
                $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
                $where[] = "group_id=" . intval($groupID);
                $where[] = "DATE(document_date + INTERVAL '+1 year -1 month +1 day')=" . Connection::GetSQLDate($date);
                $query = "SELECT receipt_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
                    " AND ",
                    $where
                ) : "");
                $receiptList = $stmt->FetchList($query);
                $receiptIDs = array_column($receiptList, "receipt_id");
            } elseif ($type == 2 || $type == 3 || $type == 4) {
                //fetch the last benefit receipt_id's
                $where = array();
                $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
                $where[] = "group_id=" . intval($groupID);
                $query = "SELECT DISTINCT ON (employee_id) receipt_id 
							FROM receipt " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " 
							ORDER BY employee_id ASC, document_date DESC";
                $lastReceiptIDs = array_column($stmt->FetchList($query), "receipt_id");

                if (count($lastReceiptIDs) > 0) {
                    if ($type == 2) {
                        /**
                         * last day of first month after receipt's period
                         * example: document_date=2018-06-10, this notification should be sent 2019-07-09
                         */
                        $where = array();
                        $where[] = "status='approved'";
                        $where[] = "archive='N'";
                        $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
                        $where[] = "group_id=" . intval($groupID);
                        $where[] = "DATE(document_date + INTERVAL '+1 year +1 month -1 day')=" . Connection::GetSQLDate($date);
                        $where[] = "receipt_id IN(" . implode(", ", $lastReceiptIDs) . ")";
                        $query = "SELECT receipt_id, employee_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
                            " AND ",
                            $where
                        ) : "");
                        $receiptList = $stmt->FetchList($query);
                    } elseif ($type == 3) {
                        /**
                         * last day of second month after receipt's period
                         * example: document_date=2017-06-10, this notification should be sent 2018-08-09
                         */
                        $where = array();
                        $where[] = "status='approved'";
                        $where[] = "archive='N'";
                        $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
                        $where[] = "group_id=" . intval($groupID);
                        $where[] = "DATE(document_date + INTERVAL '+1 year +2 month -1 day')=" . Connection::GetSQLDate($date);
                        $where[] = "receipt_id IN(" . implode(", ", $lastReceiptIDs) . ")";
                        $query = "SELECT receipt_id, employee_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
                            " AND ",
                            $where
                        ) : "");
                        $receiptList = $stmt->FetchList($query);
                    } elseif ($type == 4) {
                        /**
                         * first day of third month after receipt's period
                         * example: document_date=2017-06-10, this notification should be sent 2018-08-11
                         */
                        $where = array();
                        $where[] = "status='approved'";
                        $where[] = "archive='N'";
                        $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
                        $where[] = "group_id=" . intval($groupID);
                        $where[] = "DATE(document_date + INTERVAL '+1 year +2 month +1 day')=" . Connection::GetSQLDate($date);
                        $where[] = "receipt_id IN(" . implode(", ", $lastReceiptIDs) . ")";
                        $query = "SELECT receipt_id, employee_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
                            " AND ",
                            $where
                        ) : "");
                        $receiptList = $stmt->FetchList($query);
                    }

                    $receiptIDs = array_column($receiptList, "receipt_id");
                }
            }
        }

        return $receiptIDs;
    }

    /**
     * Returns receipt_id's of recreation receipts (type "confirmation") from payroll
     *
     * @param int $companyUnitID
     * @param string $payrollPeriod
     *
     * @return array
     */
    public static function GetReceiptIDsForConfirmationPDF($companyUnitID, $payrollPeriod)
    {
        $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        $receiptList = array();

        $where = array();
        $where[] = "r.employee_id IN(" . implode(", ", $employeeList) . ")";
        $where[] = "r.pdf_export=" . Connection::GetSQLString($payrollPeriod);
        $where[] = "r.group_id=" . Connection::GetSQLString(ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION));
        $where[] = "(r.receipt_from!='doc' OR r.receipt_from IS NULL)";

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT r.receipt_id, r.employee_id, r.document_date FROM receipt AS r" . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "");
        $receiptIDs = $stmt->FetchList($query);

        foreach ($receiptIDs as $receipt) {
            if (
                Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                    $receipt["employee_id"],
                    $receipt["document_date"]
                ) != "N"
            ) {
                continue;
            }

            $receiptList[] = $receipt;
        }

        return $receiptList;
    }

    /**
     * Returns receipt_id's of receipts without images to remove.
     * Important note - receipts should be older than a few minutes not to remove receipts user uploading image to right now
     *
     * @return array
     */
    public static function GetNoImageReceiptIDsToRemove()
    {
        $stmt = GetStatement();
        $query = "SELECT r.receipt_id, r.group_id, r.employee_id, r.created, r.receipt_from
					FROM receipt AS r 
						LEFT JOIN receipt_file AS f ON f.receipt_id=r.receipt_id
					WHERE f.receipt_file_id IS NULL AND r.created + INTERVAL '5 minutes' < " . Connection::GetSQLString(GetCurrentDateTime());
        $result = $stmt->FetchList($query);
        $receiptList = array();
        foreach ($result as $receipt) {
            if (
                ($receipt["group_id"] == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION) &&
                    Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                        $receipt["employee_id"],
                        $receipt["created"]
                    ) != "Y") ||
                ($receipt["group_id"] == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL) &&
                    Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
                        $receipt["employee_id"],
                        $receipt["created"]
                    ) != "N" &&
                        $receipt["receipt_from"] == "meal")
            ) {
                continue;
            }

            $receiptList[] = $receipt["receipt_id"];
        }

        return $receiptList;
    }

    /**
     * Returns receipt_id's of receipts with images that should had run through OCR, but couldn't
     *
     * @return array
     */
    public static function GetNoOcrImageReceiptIDs()
    {
        $stmt = GetStatement();
        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.status, r.group_id, r.employee_id, r.created, f.receipt_file_id, f.file_image
					FROM receipt AS r 
						LEFT JOIN receipt_file AS f ON f.receipt_id=r.receipt_id
					WHERE f.receipt_file_id IS NOT NULL AND needs_check='Y'";

        return $stmt->FetchList($query);
    }

    /**
     * NOT Removes receipts from database by provided ids.
     * Just make them inactive.
     *
     * @param array $ids array of receipt_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE receipt SET archive='Y' WHERE receipt_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        foreach ($ids as $id) {
            Receipt::RemoveReceiptVoucherLinks($id);
        }

        $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }

    /**
     * Revert the operation of Remove receipts by provided ids.
     *
     * @param array $ids array of receipt_id's
     */
    public function Activate($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $statusUpdate = array();
        //set status to New for service with vouchers, so we don't have problems with receipt-voucher links
        $voucherLinkServices = array_column(ProductGroupList::GetProductGroupList(false, true), "group_id");
        foreach ($ids as $id) {
            //in case links weren't deleted on removal, try to remove them on activation
            Receipt::RemoveReceiptVoucherLinks($id);

            if (!in_array(Receipt::GetReceiptFieldByID("group_id", $id), $voucherLinkServices)) {
                continue;
            }

            $statusUpdate[] = $id;
        }

        $stmt = GetStatement();

        $query = "UPDATE receipt SET archive='N' WHERE receipt_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $query = "UPDATE receipt SET status='new', status_updated=NOW() WHERE receipt_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }

    /**
     * Returns number of active employees (means have uploaded and approved min. 1 receipt)
     *
     * @param array $ids array of employeeIDs
     */
    public static function GetApprovedReceiptEmployeeIDs($ids)
    {
        if (!$ids) {
            return array();
        }

        $stmt = GetStatement();

        $where = array();
        $where[] = "status='approved'";
        $where[] = "archive='N'";
        $where[] = "employee_id IN (" . implode(", ", Connection::GetSQLArray($ids)) . ")";

        $query = "SELECT employee_id FROM receipt " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "") . " GROUP BY employee_id";

        return array_column($stmt->FetchList($query), "employee_id");
    }

    /**
     * Gets status conversion statistics for dashboard
     *
     * @param string $dateFrom start of period filters status by "created" field
     * @param string $dateTo end of period filters status by "created" field
     *
     * @return array of statistic values for 3 status conversions (new -> review, review -> approve_proposed, new -> denied)
     */
    public static function GetReceiptStatusStatisticsForDashboard($dateFrom, $dateTo)
    {
        $stmt = GetStatement(DB_CONTROL);
        $statusMap = array(
            array("status_from" => "new", "status_to" => "review"),
            array("status_from" => "review", "status_to" => "approve_proposed"),
            array("status_from" => "review", "status_to" => "denied")
        );

        foreach ($statusMap as &$status) {
            $where = array();
            if ($dateFrom) {
                $where[] = "DATE(rh1.created) >= " . Connection::GetSQLDateTime($dateFrom);
            }
            if ($dateTo) {
                $where[] = "DATE(rh1.created) <= " . Connection::GetSQLDateTime($dateTo);
            }

            $where[] = "rh1.property_name='status'";
            $where[] = "rh1.value=" . Connection::GetSQLString($status["status_to"]);

            $query = "SELECT AVG(diff), MIN(diff), MAX(diff), max_receipt_id, min_receipt_id 
                        FROM (SELECT *, first_value(r.receipt_id) OVER(ORDER BY extract(epoch from (r.created-r.created_prev)) ASC) AS max_receipt_id, first_value(r.receipt_id) OVER(ORDER BY extract(epoch from (r.created-r.created_prev))  DESC) AS min_receipt_id, extract(epoch from (r.created-r.created_prev)) AS diff 
                            FROM (SELECT DISTINCT ON(rh1.receipt_id) rh1.receipt_id, rh1.value, rh2.value AS value_prev, rh1.created, rh2.created AS created_prev
                                    FROM receipt_history AS rh1 
                                        LEFT JOIN receipt_history AS rh2 ON rh1.receipt_id=rh2.receipt_id AND rh2.property_name='status' AND rh2.created < rh1.created
                                        " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
                                    ORDER BY rh1.receipt_id, rh1.created DESC, rh2.created DESC) AS r 
                            WHERE r.value_prev=" . Connection::GetSQLString($status["status_from"]) . ") AS r
                        GROUP BY min_receipt_id, max_receipt_id";

            $status = array_merge($status, $stmt->FetchRow($query));
            $status["status_from_translation"] = GetTranslation("receipt-status-" . $status["status_from"], "receipt");
            $status["status_to_translation"] = GetTranslation("receipt-status-" . $status["status_to"], "receipt");
            if (isset($status["max"])) {
                $status["max_string"] = SecondsToString(round($status["max"]));
            }
            if (isset($status["min"])) {
                $status["min_string"] = SecondsToString(round($status["min"]));
            }
            if (!isset($status["avg"])) {
                continue;
            }

            $status["avg_string"] = SecondsToString(round($status["avg"]));
        }

        return $statusMap;
    }

    /**
     * Gets receipt list for map
     *
     * @param int $employeeID employee_id receipts to be filtered by
     * @param int $groupID group_id receipts to be filtered by
     * @param Object $receipt
     * @param array $statusList
     *
     * @return array
     */
    public static function GetReceiptListForVoucherMap(
        $employeeID,
        $groupID,
        $receipt = null,
        $statusList = ["approved", "approve_proposed"]
    ) {
        $stmt = GetStatement(DB_MAIN);
        $where = "";
        if ($receipt !== null) {
            if ($receipt->GetProperty("Save")) {
                $date = $statusDate = GetCurrentDateTime();
            } elseif ($receipt->GetProperty("document_date")) {
                $date = date("Y-m-d", strtotime($receipt->GetProperty("document_date")));
                $statusDate =
                    in_array(
                        $receipt->GetProperty("status"),
                        array("approved", "approve_proposed")
                    ) && $receipt->GetProperty("save") != 1
                 ? date("Y-m-d H:i:s", strtotime($receipt->GetProperty("status_updated"))) : GetCurrentDateTime();
            }
            $where = " AND ((document_date <= " . Connection::GetSQLString($date) . "  AND status_updated <= " . Connection::GetSQLString($statusDate) . ") OR
         (document_date > " . Connection::GetSQLString($date) . " AND status_updated <= " . Connection::GetSQLString($statusDate) . "))";
        }

        $query = "SELECT * FROM receipt
        		WHERE status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")
           			AND employee_id=" . intval($employeeID) . "
           			AND group_id=" . intval($groupID) . "
           			" . $where . "
           			AND archive='N'
        		ORDER BY document_date ASC";

        return $stmt->FetchList($query);
    }

    /**
     * Deny all receipts for previous months for company unit
     *
     * @param int $companyUnitID company_unit_id
     * @param string $date date
     */
    public static function DenyOldReceipts($companyUnitID, $date)
    {
        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        $created = date("Y-m-01", strtotime($date));

        $where = array();
        $where[] = "status NOT IN ('approved', 'denied', 'approve_proposed')";
        $where[] = "archive='N'";
        $where[] = "employee_id IN (" . implode(", ", $employeeIDs) . ")";
        $where[] = "DATE(created)<" . Connection::GetSQLDate($created);

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT * FROM receipt
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $receiptList = $stmt->FetchList($query);
        $employee = new Employee("company");

        foreach ($receiptList as $receipt) {
            $employee->LoadByID($receipt["employee_id"]);

            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name")
            );

            $template = Config::GetConfigValue("message_automatic_deny_old_receipt");
            $message = GetLanguage()->ReplacePairs($template, $replacements);
            $denialReason = Config::GetConfigValue('receipt_autodeny_old_receipt');

            Receipt::UpdateField($receipt["receipt_id"], "status", "denied");
            Receipt::UpdateField($receipt["receipt_id"], "automatic_processed", "Y");
            Receipt::UpdateField($receipt["receipt_id"], 'denial_reason', Connection::GetSQLString($denialReason));

            $receiptComment = new ReceiptComment("receipt");
            $receiptComment->SetProperty("receipt_id", $receipt["receipt_id"]);
            $receiptComment->SetProperty("user_id", SERVICE_USER_ID);
            $receiptComment->SetProperty("content", $message);
            $receiptComment->SetProperty("read_by_admin", "Y");
            $receiptComment->Create();
        }
    }

    /*
    * @param $groupId
    * @param $employeeId
    *
    * @return int
    */
    public static function GetNumberNecessaryActionsByEmployee($groupId, $employeeId, $tripId = false)
    {
        $currentDate = GetCurrentDate();
        $dateFrom = date("Y-m-01", strtotime($currentDate . " -1 month"));
        $dateTo = date("Y-m-t", strtotime($currentDate));

        $where = array();
        $where[] = "employee_id=" . intval($employeeId);

        if ($groupId) {
            $where[] = "group_id=" . intval($groupId);
        }
        if ($tripId) {
            $where[] = "trip_id=" . intval($tripId);
        }

        $where[] = "status='approve_proposed'";
        $where[] = "archive='N'";
        $where[] = "DATE(created) >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "DATE(created) <= " . Connection::GetSQLDate($dateTo);

        $stmt = GetStatement();
        $query = "SELECT COUNT(receipt_id) FROM receipt
		     " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        return intval($stmt->FetchField($query));
    }

    public function GetReceiptListOCRStatisticsForDashboard($dateFrom, $dateTo, $timeGroup = "minute")
    {
        switch ($timeGroup) {
            case "hour":
                $phpFormat = "m/d/Y H:00:00";
                $sqlFormat = "MM/DD/YYYY HH24:00:00";
                $iPlus = 3600;
                break;
            case "minute":
                $phpFormat = "m/d/Y H:i:00";
                $sqlFormat = "MM/DD/YYYY HH24:MI:00";
                $iPlus = 60;
                break;
            case "second":
                $phpFormat = "m/d/Y H:i:s";
                $sqlFormat = "MM/DD/YYYY HH24:MI:SS";
                $iPlus = 1;
                break;
            default:
                $phpFormat = "m/d/Y H:i:00";
                $sqlFormat = "MM/DD/YYYY HH24:MI:00";
                $iPlus = 60;
                break;
        }

        $limit = 10000;

        $dateList = array();
        $dateListMap = array();
        $i = strtotime($dateFrom);
        while (count($dateListMap) < $limit && $i < strtotime($dateTo)) {
            $dateListMap[date($phpFormat, $i)]["receipt_count"] = 0;
            $dateListMap[date($phpFormat, $i)]["ocr_all_count"] = 0;
            $dateListMap[date($phpFormat, $i)]["ocr_receipt_count"] = 0;
            $dateListMap[date($phpFormat, $i)]["ocr_no_receipt_count"] = 0;
            $dateListMap[date($phpFormat, $i)]["ocr_unsuccessful_count"] = 0;
            $dateList[] = array(
                "date" => date($phpFormat, $i),
                "receipt_count" => 0,
                "ocr_all_count" => 0,
                "ocr_receipt_count" => 0,
                "ocr_no_receipt_count" => 0,
                "ocr_unsuccessful_count" => 0
            );
            $i += $iPlus;
        }

        if ($i < strtotime($dateTo)) {
            $this->AddMessage("limit-ocr-check-exceeded", $this->module, ["limit" => $limit]);
            $dateTo = date("Y-m-d H:i:s", $i);
        }

        $stmtMain = GetStatement(DB_MAIN);
        $stmtControl = GetStatement(DB_CONTROL);
        $whereReceipt = array();
        $whereOcrRequest = array();
        if ($dateFrom) {
            $whereReceipt[] = "r.created >= " . Connection::GetSQLDateTime($dateFrom);
            $whereOcrRequest[] = "o1.created >= " . Connection::GetSQLDateTime($dateFrom);
        }
        if ($dateTo) {
            $whereReceipt[] = "r.created <= " . Connection::GetSQLDateTime($dateTo);
            $whereOcrRequest[] = "o1.created <= " . Connection::GetSQLDateTime($dateTo);
        }

        $productGroupList = ProductGroupList::GetProductGroupList("Y");
        $productGroupIDs = array_column($productGroupList, "group_id");

        $whereReceipt[] = "r.group_id IN (" . implode(",", Connection::GetSQLArray($productGroupIDs)) . ")";
        $whereOcrRequest[] = "o1.type = 'ocr_1'";

        $queryReceipt = "SELECT TO_CHAR(r.created, '" . $sqlFormat . "') AS date, COUNT(receipt_id) AS receipt_count FROM receipt AS r
             " . (count($whereReceipt) > 0 ? " WHERE " . implode(" AND ", $whereReceipt) : "") . "
             GROUP BY TO_CHAR(r.created, '" . $sqlFormat . "') ORDER BY date";

        $queryOcrRequest = "SELECT TO_CHAR(o1.created, '" . $sqlFormat . "') AS date, COUNT(o1.request_id) AS ocr_all_count, COUNT(o2.request_id) AS ocr_receipt_count, COUNT(o3.request_id) AS ocr_no_receipt_count, COUNT(o4.request_id) AS ocr_unsuccessful_count 
                            FROM ocr_request AS o1
                                LEFT JOIN ocr_request AS o2 ON o1.request_id=o2.request_id AND o2.is_successful='Y' AND o2.is_receipt='Y'
                                LEFT JOIN ocr_request AS o3 ON o1.request_id=o3.request_id AND o3.is_successful='Y' AND o3.is_receipt='N'
                                LEFT JOIN ocr_request AS o4 ON o1.request_id=o4.request_id AND o4.is_successful='N'
                         " . (count($whereOcrRequest) > 0 ? " WHERE " . implode(" AND ", $whereOcrRequest) : "") . "
                         GROUP BY TO_CHAR(o1.created, '" . $sqlFormat . "') ORDER BY date";

        $resultReceipt = $stmtMain->FetchList($queryReceipt);
        $resultOcrRequest = $stmtControl->FetchList($queryOcrRequest);
        if ($resultReceipt) {
            $resultReceiptMap = array_combine(array_column($resultReceipt, "date"), $resultReceipt);
            $dateListMap = array_merge_recursive2($dateListMap, $resultReceiptMap);
        }
        $resultOcrRequestMap = false;
        if ($resultOcrRequest) {
            $resultOcrRequestMap = array_combine(array_column($resultOcrRequest, "date"), $resultOcrRequest);
            //if($resultReceipt)
            //    $resultOcrRequestMap = array_merge_recursive2($resultReceiptMap, $resultOcrRequestMap);
            $dateListMap = array_merge_recursive2($dateListMap, $resultOcrRequestMap);
        }
        if ($resultReceipt || $resultOcrRequestMap) {
            $dateList = array();
            foreach ($dateListMap as $key => $value) {
                $dateList['graph'][] = array(
                    "date" => $key,
                    "receipt_count" => $value["receipt_count"],
                    "ocr_all_count" => $value["ocr_all_count"],
                    "ocr_receipt_count" => $value["ocr_receipt_count"],
                    "ocr_no_receipt_count" => $value["ocr_no_receipt_count"],
                    "ocr_unsuccessful_count" => $value["ocr_unsuccessful_count"]
                );
            }
        }
        //Get details (usernames and counts reciepts) for label on graphs (mouse hover)
        $queryReceiptForLabel = "SELECT TO_CHAR(r.created, '" . $sqlFormat . "') AS date, COUNT(receipt_id) AS receipt_count, employee_id AS employee_id FROM receipt AS r
             " . (count($whereReceipt) > 0 ? " WHERE " . implode(" AND ", $whereReceipt) : "") . "
             GROUP BY TO_CHAR(r.created, '" . $sqlFormat . "'), employee_id ORDER BY date";

        $queryOcrRequestForLabel = "SELECT TO_CHAR(o1.created, '" . $sqlFormat . "') AS date, COUNT(o1.request_id) AS ocr_all_count, COUNT(o2.request_id) AS ocr_receipt_count, COUNT(o3.request_id) AS ocr_no_receipt_count, COUNT(o4.request_id) AS ocr_unsuccessful_count, o1.user_id AS user_id
                    FROM ocr_request AS o1
                        LEFT JOIN ocr_request AS o2 ON o1.request_id=o2.request_id AND o2.is_successful='Y' AND o2.is_receipt='Y'
                        LEFT JOIN ocr_request AS o3 ON o1.request_id=o3.request_id AND o3.is_successful='Y' AND o3.is_receipt='N'
                        LEFT JOIN ocr_request AS o4 ON o1.request_id=o4.request_id AND o4.is_successful='N'
                 " . (count($whereOcrRequest) > 0 ? " WHERE " . implode(" AND ", $whereOcrRequest) : "") . "
                 GROUP BY TO_CHAR(o1.created, '" . $sqlFormat . "'), o1.user_id ORDER BY date";

        $resultReceiptForLabel = $stmtMain->FetchList($queryReceiptForLabel);
        $resultOcrRequestForLabel = $stmtControl->FetchList($queryOcrRequestForLabel);

        if ($resultReceiptForLabel) {
            foreach ($resultReceiptForLabel as $key => $value) {
                $dateList['labelReceipt'][] = array(
                    "date" => $value["date"],
                    "employee" => Employee::GetNameByID($value["employee_id"]),
                    "receipt_count" => $value["receipt_count"]
                );
            }
        }

        if ($resultOcrRequestForLabel) {
            foreach ($resultOcrRequestForLabel as $key => $value) {
                $dateList['labelOcrRequest'][] = array(
                    "date" => $value["date"],
                    "user" => User::GetNameByID($value["user_id"]),
                    "ocr_all_count" => $value["ocr_all_count"],
                    "ocr_receipt_count" => $value["ocr_receipt_count"],
                    "ocr_no_receipt_count" => $value["ocr_no_receipt_count"],
                    "ocr_unsuccessful_count" => $value["ocr_unsuccessful_count"]
                );
            }
        }

        return $dateList;
    }

    /**
     * Loads receipt list for voucher
     *
     * @param int $voucherID voucher ID
     */
    public function GetReceiptListForVoucher($voucherID, $languageCode = null)
    {
        $stmt = GetStatement();

        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.created, r.updated, r.status, r.group_id, r.document_date, v.amount, ve.created AS date_payment 
				  FROM receipt AS r
				  RIGHT JOIN voucher_receipt AS v ON v.receipt_id=r.receipt_id
				  LEFT JOIN voucher_export_datev AS ve ON ve.export_id=r.creditor_export_id
        		  WHERE voucher_id=" . Connection::GetSQLString($voucherID) . "
        		  ORDER BY r.updated DESC";

        $receiptList = $stmt->FetchList($query);

        for ($i = 0; $i < count($receiptList); $i++) {
            $productGroupCode = ProductGroup::GetProductGroupCodeByID($receiptList[$i]["group_id"]);
            $receiptList[$i]["status_title"] = GetTranslation(
                "receipt-status-" . $receiptList[$i]["status"],
                $this->module,
                null,
                $languageCode
            );
            $receiptList[$i]["group_title_translation"] = GetTranslation(
                "product-group-" . $productGroupCode,
                "product",
                null,
                $languageCode
            );
        }

        return $receiptList;
    }

    /**
     * Get receipt IDs for generate stored data
     *
     * @param string $dateFrom beginning of period stored data
     * @param string $dateTo end of period stored data
     * @param array $employees employees IDs
     *
     * @return array receipt IDs
     */
    static function GetReceiptIDsForStoredData($dateFrom, $dateTo, $employees)
    {
        $where = array();
        $where[] = "status = 'approved'";
        $where[] = "DATE(document_date) >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($dateTo);
        $where[] = "employee_id IN(" . implode(',', $employees) . ")";

        //don't include receipts from voucher based services into stored data (#3749)
        $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, "Y", false, false);
        $where[] = "group_id NOT IN (" . implode(", ", array_column($voucherProductGroupList, "group_id")) . ")";

        $stmt = GetStatement();
        $query = "SELECT receipt_id FROM receipt WHERE " . implode(" AND ", $where);

        $receiptList = $stmt->FetchList($query);

        return array_column($receiptList, "receipt_id");
    }

    public static function GetLastApprovedReceipt($employeeID, $groupID, $date, $statusList, $limit = 0)
    {
        $specificProductGroup = SpecificProductGroupFactory::Create($groupID);
        $numberOfPaymentMonth = $specificProductGroup->GetNumberOfPaymentMonth($employeeID, $date);

        $stmt = GetStatement();
        $where = [];
        $where[] = "employee_id=" . $employeeID;
        $where[] = "group_id=" . intval($groupID);
        $where[] = "DATE(document_date) <= " . Connection::GetSQLDate(date("Y-m-t", strtotime($date)));
        $where[] = "DATE(document_date) + " . $numberOfPaymentMonth . " * INTERVAL '1 month' > 
                    " . Connection::GetSQLDate(date("Y-m-t", strtotime($date)));
        $where[] = "status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")";
        $where[] = "archive='N'";
        if (intval($limit) > 0) {
            $where[] = "real_amount_approved > 0";
        }
        $query = "SELECT receipt_id, legal_receipt_id, employee_id, group_id, created,
                    amount_approved, real_amount_approved, document_date, status,
                    pdf_export, datev_export
    				FROM receipt "
            . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "") . "
    				ORDER BY document_date DESC";
        if (intval($limit) > 0) {
            $query .= " LIMIT " . intval($limit);
        }

        return $stmt->FetchList($query);
    }

    public static function GetLinksForReceiptList($receiptList)
    {
        $receiptColumnID = array_column($receiptList, "receipt_id");

        if (count($receiptColumnID) > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT vr.receipt_id, vr.voucher_id, vr.amount as link_amount, vr.created,
                            v.amount as voucher_amount
                        FROM voucher_receipt vr
                            LEFT JOIN voucher v ON v.voucher_id=vr.voucher_id
                        WHERE receipt_id IN
                              (" . implode(", ", $receiptColumnID) . ")";
            $linkList = $stmt->FetchList($query);

            foreach ($linkList as $link) {
                $key = array_search($link["receipt_id"], $receiptColumnID);
                if ($key === false) {
                    continue;
                }

                $receiptList[$key]["link_list"][] = $link;
            }

            foreach ($receiptList as $key => $receipt) {
                if (isset($receipt["link_list"])) {
                    continue;
                }

                $receiptList[$key]["link_list"] = [];
            }
        }

        return $receiptList;
    }

    public static function GetVatReport($request, $returnArray = false) {
        //sorting by voucher type
        $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        $groupList = [];
        foreach ($voucherProductGroupList as $productGroup) {
            $productGroup["title_translation"] = GetTranslation("product-group-" . $productGroup["code"], "product");
            $groupList[$productGroup["group_id"]] = $productGroup;
        }
        if (!$request->IsPropertySet("voucher_type")) {
            $groupIDs = array_column($voucherProductGroupList, "group_id");
        } else {
            $groupIDs = $request->GetProperty("voucher_type");
        }

        //sorting by date
        $where = [];
        if ($request->GetProperty("start_date") != null) {
            $startDate = date("Ym", strtotime("01." . $request->GetProperty("start_date")));
            $where[] = "(export_month >= " . Connection::GetSQLString($startDate) . "
             OR export_month < " . Connection::GetSQLString($startDate) . "
              AND created > " . Connection::GetSQLString(date("Y-m-d", strtotime("01." . $request->GetProperty("start_date")))) . ")";
        }
        if ($request->GetProperty("end_date") != null) {
            $endDate = date("Ym", strtotime("01." . $request->GetProperty("end_date")));
            $where[] = "export_month <= " . Connection::GetSQLString($endDate);
        }

        //exported
        $stmt = GetStatement();
        $query = "SELECT export_id, created, export_number, export_month
                    FROM voucher_export_datev
                        " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "") . "
                    ORDER BY created";
        $exportList = $stmt->FetchList($query);

        //calculating exported values
        $exportIDs = array_column($exportList, "export_id");

        $query = "SELECT receipt_id, legal_receipt_id, real_amount_approved, vat, group_id, creditor_export_id, status_updated, status
            FROM receipt
                WHERE
                   creditor_export_id IN (" . implode(", ", Connection::GetSQLArray($exportIDs)) . ")
                   AND group_id IN (" . implode(", ", Connection::GetSQLArray($groupIDs)) . ")
                   " . ($request->IsPropertySet("vat") && $request->GetProperty("vat") != "total"
                        ? " AND vat = " . Connection::GetSQLString($request->GetProperty('vat')) : "") . "
                   AND status = 'approved'
                   AND archive = 'N'
                   AND vat IS NOT NULL
               ORDER BY vat DESC, legal_receipt_id ASC";
        $exportedList = $stmt->FetchList($query);
        $exportedSum = 0;

        //group receipts by VAT and calculate percentage
        if (!$returnArray) {
            $vatList = [];
            foreach ($exportedList as $receipt) {
                $vatColumn = array_column($vatList, "vat");
                $key = array_search($receipt["vat"], $vatColumn);
                if ($key === false) {
                    $vatList[] = [
                        "vat" => $receipt["vat"],
                        "sum" => 0,
                        "percentage_sum" => 0,
                    ];
                    $key = count($vatList) - 1;
                }

                $vatList[$key]["sum"] += $receipt["real_amount_approved"];
                $percentage = $receipt["real_amount_approved"] * 100 / (100 + $receipt["vat"]);
                $vatList[$key]["percentage_sum"] += round($percentage, 2);
            }
            $exportedList = $vatList;
        } else {
            foreach ($exportedList as $key => $receipt) {
                $exportedList[$key]["voucher_type"] = $groupList[$receipt["group_id"]]["title_translation"];
                $exportedSum += $receipt["real_amount_approved"];
            }
        }

        //calculating approved values
        if (!empty($exportList)) {
            $startDate = $stmt->FetchField(
                "SELECT created FROM voucher_export_datev
                    WHERE created < " . Connection::GetSQLDateTime($exportList[0]["created"]) . "
                    ORDER BY created DESC"
            );
            $count = count($exportList);

            $currDate = date("Ym", strtotime(GetCurrentDate()));
            if ($endDate != null && $exportList[$count - 1]["export_month"] == $endDate && $endDate != $currDate) {
                $endDate =  $exportList[$count - 1]["created"];
            } else {
                $endDate = $request->GetProperty("end_date") != null ?
                    date("Y-m-t", strtotime("01." . $request->GetProperty("end_date"))) :
                    GetCurrentDateTime();
            }
        } else {
            $endDate = GetCurrentDateTime();
            $startDate = $stmt->FetchField(
                "SELECT created FROM voucher_export_datev
                    WHERE created < " . Connection::GetSQLDateTime($endDate) . "
                    ORDER BY created DESC"
            );
        }

        $query = "SELECT receipt_id, legal_receipt_id, real_amount_approved, vat, group_id, creditor_export_id, status_updated, status
            FROM receipt
                WHERE
                   (status_updated < " . Connection::GetSQLDateTime($endDate) . "
                   " . ($startDate != null ? "AND status_updated > " . Connection::GetSQLDateTime($startDate) : "") . "
                   " . (!empty($exportIDs) ? " OR creditor_export_id IN (" . implode(", ", Connection::GetSQLArray($exportIDs)) . ")" : "") . ")
                   AND group_id IN (" . implode(", ", Connection::GetSQLArray($groupIDs)) . ")
                   AND status = 'approved'
                   " . ($request->IsPropertySet("vat") && $request->GetProperty("vat") != "total"
                        ? " AND vat = " . Connection::GetSQLString($request->GetProperty('vat')) : "") . "
                   AND archive = 'N'
                   AND vat IS NOT NULL
               ORDER BY vat DESC, legal_receipt_id ASC";
        $approvedList = $stmt->FetchList($query);
        $approvedSum = 0;

        //group receipts by VAT and calculate percentage
        if (!$returnArray) {
            $vatList = [];
            foreach ($approvedList as $receipt) {
                $vatColumn = array_column($vatList, "vat");
                $key = array_search($receipt["vat"], $vatColumn);
                if ($key === false) {
                    $vatList[] = [
                        "vat" => $receipt["vat"],
                        "sum" => 0,
                        "percentage_sum" => 0,
                    ];
                    $key = count($vatList) - 1;
                }

                $vatList[$key]["sum"] += $receipt["real_amount_approved"];
                $percentage = $receipt["real_amount_approved"] * 100 / (100 + $receipt["vat"]);
                $vatList[$key]["percentage_sum"] += round($percentage, 2);
            }
            $approvedList = $vatList;
        } else {
            foreach ($approvedList as $key => $receipt) {
                $approvedList[$key]["voucher_type"] = $groupList[$receipt["group_id"]]["title_translation"];
                $approvedSum += $receipt["real_amount_approved"];
            }
        }

        return [
            "exported" => $exportedList,
            "exported_total" => $exportedSum,
            "approved" => $approvedList,
            "approved_total" => $approvedSum,
        ];
    }
}
