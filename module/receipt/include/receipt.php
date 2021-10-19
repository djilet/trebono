<?php

require_once(dirname(__FILE__) . "/../../agreements/include/confirmation.php");

class Receipt extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of receipt properties to be loaded instantly
     */
    public function Receipt($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads receipt by its receipt_id
     *
     * @param int $id receipt_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.created, r.document_guid, r.document_date, r.document_date_from, r.document_date_to, r.datev_export,
						r.amount_approved, r.real_amount_approved, r.status, r.status_user_id, r.status_updated, r.updated, r.store_name, r.receipt_from, r.version_id, r.denial_reason,
                        r.comment, r.trip_id, t.trip_name, r.booked, r.ref_number, r.acc_system, r.currency_id, r.vat, r.days_amount_under_16, r.days_amount_over_16, r.sets_of_goods,
                        r.creditor_export_id, r.is_web_upload
					FROM receipt AS r 
                        LEFT JOIN trip as t ON r.trip_id=t.trip_id
					WHERE r.receipt_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("receipt_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareBeforeShow()
    {
        if ($this->GetProperty("version_id")) {
            $device = Device::GetDeviceByVersionID($this->GetProperty("version_id"));
            if (is_array($device)) {
                $this->AppendFromArray($device);
            }
        }
        if ($this->ValidateNotEmpty("document_date")) {
            return;
        }

        $this->SetProperty("document_date", GetCurrentDateTime());
    }

    /**
     * Loads receipt by its receipt_id for api
     *
     * @param int $id receipt_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadForApi($id)
    {
        $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.created, r.updated,
						CASE r.status WHEN 'supervisor' THEN 'review' ELSE r.status END AS status,
						r.document_guid, r.amount_approved, r.real_amount_approved, r.store_name,
						r.document_date, r.receipt_from, r.trip_id, cr.digit AS currency, r.sets_of_goods,
						g.code AS group_code, r.vat, r.days_amount_under_16, r.days_amount_over_16,
						SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) AS unread_comment_count_employee,
						(CASE WHEN SUM(CASE c.read_by_employee WHEN 'N' THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) AS has_unread_comment_employee 
					FROM receipt AS r 
						LEFT JOIN product_group AS g ON g.group_id=r.group_id
						LEFT JOIN receipt_comment AS c ON c.receipt_id=r.receipt_id 
						LEFT JOIN currency AS cr ON r.currency_id=cr.currency_id
					WHERE r.receipt_id=" . intval($id) . " 
					GROUP BY r.receipt_id, g.code, cr.digit";
        $this->LoadFromSQL($query);

        if (!$this->GetProperty("receipt_id")) {
            return false;
        }

        $this->SetProperty(
            "group_title_translation",
            GetTranslation("product-group-" . $this->GetProperty("group_code"), "product")
        );
        if ($this->GetProperty("receipt_from")) {
            $this->SetProperty(
                "receipt_type_title_translation",
                GetTranslation("receipt-type-" . $this->GetProperty("receipt_from"), "product")
            );
        } else {
            $this->SetProperty("receipt_type_title_translation", "");
        }
        $this->SetProperty(
            "confirmation_description",
            GetTranslation($this->GetProperty("group_code") . "-api-confirmation_description", "product")
        );
        $this->SetProperty(
            "status_title",
            GetTranslation("receipt-status-" . $this->GetProperty("status"), $this->module)
        );

        if ($specificProductGroup = SpecificProductGroupFactory::Create($this->GetProperty("group_id"))) {
            $this->SetProperty("api_real_amount_approved", $specificProductGroup->GetApiRealAmountApproved($this));
        } else {
            $this->SetProperty("api_real_amount_approved", 0);
        }

        if ($this->GetProperty("trip_id")) {
            $trip = new Trip("company");
            $trip->LoadByID($this->GetProperty("trip_id"));

            $this->SetProperty("trip_name", $trip->GetProperty("trip_name"));
            $this->SetProperty("finished_by_employee", $trip->GetProperty("finished_by_employee"));
            $this->SetProperty("finished_by_admin", $trip->GetProperty("finished_by_admin"));
            $this->SetProperty("purpose", $trip->GetProperty("purpose"));
            $this->SetProperty("start_date", $trip->GetProperty("start_date"));
            $this->SetProperty("end_date", $trip->GetProperty("end_date"));
        }

        if (
            $this->GetIntProperty("group_id") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION) &&
            Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                $this->GetIntProperty("employee_id"),
                $this->GetProperty('document_date')
            ) == "N"
        ) {
            $employee = new Employee("company");
            $material_status = $employee->GetPropertyHistoryValueEmployee(
                "material_status",
                $this->GetProperty("employee_id"),
                $this->GetProperty("created")
            );

            if ($material_status["value"] == "single") {
                $materialStatus = GetTranslation("material-status-single", "company");
            }
            if ($material_status["value"] == "married") {
                $materialStatus = GetTranslation("material-status-married", "company");
            }

            $this->SetProperty("material_status", $materialStatus);
            $child_count = $employee->GetPropertyHistoryValueEmployee(
                "child_count",
                $this->GetProperty("employee_id"),
                $this->GetProperty("created")
            );
            $this->SetProperty("child_count", $child_count["value"]);
        }

        if ($this->GetProperty("currency") == null) {
            $this->SetProperty("currency", Currency::GetSymbolByID(Currency::GetDefaultID()));
        }

        return true;
    }

    /**
     * Creates receipt from mobile application. Object must be loaded from request before the method will be called.
     * Required properties are: employee_id, group_id, user_id
     *
     * @return bool true if receipt is created successfully or false on failure
     */
    public function Create()
    {
        $recreationWithoutPicture = $this->GetIntProperty("group_id") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION) &&
            Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                $this->GetIntProperty("employee_id"),
                GetCurrentDateTime()
            ) == "N";
        if ($recreationWithoutPicture && (!$this->IsPropertySet("material_status") || !$this->IsPropertySet("child_count"))) {
            $this->AddError("recreation-fields-missing", $this->module);

            return false;
        }
        $dailyAllowance = (Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
            $this->GetIntProperty("employee_id"),
            GetCurrentDateTime()
        ) && $this->GetProperty("receipt_from") == "meal");
        $versionID = Device::GetLastVersionIDByDeviceID($this->GetProperty("device_id"), $this->GetProperty("user_id"));

        //make sure that amount can be written from app only to travel service
        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($this->GetIntProperty("group_id"));

        if ($productGroup->GetProperty("code") != PRODUCT_GROUP__TRAVEL) {
            $this->SetProperty("amount_approved", null);
        } else {
            if ($dailyAllowance == "Y") {
                $realAmountApproved =
                    $this->GetProperty("days_amount_under_16") * Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__TRAVEL__MAIN__HOURS_UNDER,
                        $this->GetProperty("employee_id"),
                        GetCurrentDateTime()
                    )
                    + $this->GetProperty("days_amount_over_16") * Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__TRAVEL__MAIN__HOURS_OVER,
                        $this->GetProperty("employee_id"),
                        GetCurrentDateTime()
                    );
                $this->SetProperty("amount_approved", $realAmountApproved);
            }
        }

        //temporary, in future take from config (3973)
        if (
            $productGroup->GetProperty("code") == PRODUCT_GROUP__TRAVEL ||
            $productGroup->GetProperty("voucher") == "Y"
        ) {
            if (!$this->IsPropertySet("vat")) {
                if (
                    ($productGroup->GetProperty("code") == PRODUCT_GROUP__FOOD_VOUCHER &&
                        $this->GetProperty("receipt_from") == "shop") ||
                    ($productGroup->GetProperty("code") == PRODUCT_GROUP__BENEFIT_VOUCHER &&
                        $this->GetProperty("sets_of_goods") == "alles für deine Ernährung")
                ) {
                    $this->SetProperty("vat", 7);
                } else {
                    $this->SetProperty("vat", 19);
                }
            }
            if (!$this->GetProperty("currency_id")) {
                $this->SetProperty("currency_id", Currency::GetDefaultID());
            }
        }

        if (!$this->IsPropertySet("status")) {
            $this->SetProperty("status", "new");
        }

        $stmt = GetStatement();
        $query = "INSERT INTO receipt (employee_id, group_id, created, status, status_user_id, status_updated, updated, receipt_from, trip_id, version_id, currency_id, amount_approved, vat, days_amount_over_16, days_amount_under_16, sets_of_goods, denial_reason, is_web_upload) VALUES (
						" . $this->GetIntProperty("employee_id") . ",
						" . $this->GetIntProperty("group_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("status") . ",						
						" . $this->GetIntProperty("user_id") . ", 
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $this->GetPropertyForSQL("receipt_from") . ",
                        " . ($this->GetProperty("trip_id") ? $this->GetIntProperty("trip_id") : "NULL") . ",
                        " . intval($versionID) . ",
                        " . $this->GetIntProperty("currency_id") . ",
                        " . $this->GetPropertyForSQL("amount_approved") . ",
                        " . $this->GetPropertyForSQL("vat") . ",
                        " . $this->GetPropertyForSQL("days_amount_over_16") . ",
                        " . $this->GetPropertyForSQL("days_amount_under_16") . ",
                        " . $this->GetPropertyForSQL("sets_of_goods") . ",
                        " . ($this->GetProperty("denial_reason") ? Connection::GetSQLString($this->GetProperty("denial_reason")) : "NULL") . ",
                        " . $this->GetPropertyForSQL("is_web_upload") . ")
					RETURNING receipt_id";
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        $this->SetProperty("receipt_id", $stmt->GetLastInsertID());

        if (IsLocalEnvironment()) {
            Receipt::SetLegalReceiptID($this->GetProperty("receipt_id"));
        }

        if ($dailyAllowance || $recreationWithoutPicture) {
            Receipt::SetLegalReceiptID($this->GetProperty("receipt_id"));
        }
        if ($recreationWithoutPicture) {
            Receipt::SetLegalReceiptID($this->GetProperty("receipt_id"));

            $employee = new Employee($this->module);
            $employee->LoadByID($this->GetIntProperty("employee_id"));
            $employee->SetProperty("material_status", $this->GetProperty("material_status"));
            $employee->SetProperty("child_count", $this->GetProperty("child_count"));

            if (!$employee->SaveHistory(false)) {
                $query = "DELETE FROM receipt WHERE receipt_id = " . $this->GetPropertyForSQL("receipt_id");
                $stmt->Execute($query);
                $this->AddError("sql-error");

                return false;
            }
            $query = "UPDATE receipt SET 
                document_date=" . Connection::GetSQLString(GetCurrentDateTime())
                . " WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
            $stmt->Execute($query);

            //if new receipt in recreation group, need update receipt: set status approve_proposed and calculate amount
            $receipt = new Receipt($this->module);
            $receipt->LoadByID($this->GetIntProperty("receipt_id"));
            if ($receipt->GetProperty("status") != "denied") {
                $receipt->SetProperty("status", "approve_proposed");
                $receipt->Update();
            }
        }
        $propertyList = array(
            "document_guid",
            "amount_approved",
            "real_amount_approved",
            "status",
            "group_id",
            "document_date",
            "document_date_from",
            "document_date_to",
            "store_name",
            "receipt_from",
            "comment",
            "vat",
            "currency_id",
            "amount_approved",
            "days_amount_under_16",
            "days_amount_over_16",
            "sets_of_goods"
        );
        $propertyList = array_fill_keys($propertyList, "");
        if (!$this->SaveHistory($propertyList, $this->GetIntProperty("user_id"), true)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Generates and saves to db legal_receipt_id
     *
     * @param int $receiptID receipt_id of receipt legal_receipt_id to be generated for
     */
    public static function SetLegalReceiptID($receiptID)
    {
        $stmt = GetStatement();
        $query = "UPDATE receipt SET legal_receipt_id = nextval('receipt_legal_receipt_id_seq')
					WHERE receipt_id=" . intval($receiptID) . " AND legal_receipt_id IS NULL";
        $stmt->Execute($query);
    }

    /**
     * Updates the receipt from admin panel. Object must be loaded from request before the method will be called.
     * Required properties are: receipt_id, group_id, status, document_guid, document_date, amount_approved
     *
     * @return bool true if receipt is updated successfully or false on failure
     */
    public function Update()
    {
        $specificProductGroup = SpecificProductGroupFactory::Create($this->GetProperty("group_id"));
        if ($specificProductGroup === null) {
            return false;
        }

        if ($this->GetProperty("EMPLOYEE_internal_verification_info")) {
            $optionCode = OPTIONS_INTERNAL_VERIFICATION_INFO[$specificProductGroup->GetMainProductCode()];

            $option = new Option("product");
            $option->SaveOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                Option::GetOptionIDByCode($optionCode),
                $this->GetProperty("EMPLOYEE_internal_verification_info"),
                $this->GetIntProperty("employee_id")
            );
        }

        $optionsForSaving = [
            OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
            OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
            OPTION__FOOD__MAIN__IMPORTANT_INFO,
            OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO,
            OPTION__MOBILE__MAIN__MOBILE_MODEL,
            OPTION__MOBILE__MAIN__MOBILE_NUMBER,
        ];

        foreach ($optionsForSaving as $optionForSaving) {
            if (!$this->GetProperty("EMPLOYEE_" . $optionForSaving)) {
                continue;
            }

            $option = new Option("product");
            $option->SaveOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                (int)Option::GetOptionIDByCode($optionForSaving),
                $this->GetProperty("EMPLOYEE_" . $optionForSaving),
                $this->GetIntProperty("employee_id")
            );
        }

        $stmt = GetStatement();
        $query = "SELECT status FROM receipt WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
        $prevStatus = $stmt->FetchField($query);
        $statusChanged = $prevStatus != $this->GetProperty("status");

        $receiptBefore = new Receipt($this->module);
        $receiptBefore->LoadByID($this->GetProperty("receipt_id"));

        //temporarily commented due to #2007
        //if($prevStatus == "approved" || $prevStatus == "denied")
        //return true;

        if ($receiptBefore->GetProperty("datev_export") != 0) {
            return true;
        }
        /*---Block for taking value of amount approved---*/
        /*Change due #1975*/

        /*$query = "SELECT ROUND(SUM(quantity * price), 2) FROM receipt_line WHERE receipt_id=".$this->GetIntProperty("receipt_id")." AND approved='Y'";
        $amountApproved = floatval($stmt->FetchField($query));
        $this->SetProperty("amount_approved", $amountApproved);*/

        //there is input exception: entered point should be ignored, only comma separates integer and fractional parts
        $amountApproved = $this->GetProperty("amount_approved");
        $amountApproved = preg_match("/[,]/", $amountApproved) ? preg_replace("/[^0-9,]/", "", $amountApproved) : preg_replace("/[^0-9.]/", "", $amountApproved);
        $amountApproved = str_replace(",", ".", $amountApproved);
        $amountApproved = floatval($amountApproved);
        $this->SetProperty("amount_approved", $amountApproved);
        /*-----------------------------------------------*/
        if (!$this->ValidateUpdate($prevStatus)) {
            $this->PrepareBeforeShow();

            return false;
        }

        $newVoucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        $isNewVoucherProductGroup = in_array(
            $this->GetProperty("group_id"),
            array_column($newVoucherProductGroupList, "group_id")
        );

        if ($isNewVoucherProductGroup && $this->GetProperty("status" == "approved")) {
            $documentDateBefore = date("Y-m-d", strtotime($receiptBefore->GetProperty("document_date")));
            $documentDateAfter = date("Y-m-d", strtotime($this->GetProperty("document_date")));

            if ($documentDateBefore != $documentDateAfter) {
                $this->SetProperty("status", "approve_proposed");
            }
        }

        $this->SetProperty("Save", 1);

        if ($this->GetProperty("status") == "approve_proposed") {
            if (!$specificProductGroup->ValidateReceiptApprove($this)) {
                return false;
            }
            if (!$this->ValidateAdvancedSecurity()) {
                return false;
            }
        }

        if ($isNewVoucherProductGroup && in_array($this->GetProperty("status"), ["approve_proposed", "approved"])) {
            $voucherLinks = self::GetVoucherReceiptLinks($this->GetProperty("receipt_id"));
            $voucherZero = false;
            foreach ($voucherLinks as $link) {
                if ($link["amount"] != 0) {
                    continue;
                }

                $voucherZero = true;
            }

            if (count($voucherLinks) == 0 || $voucherZero) {
                $this->AddError("approved-receipt-has-no-mapping", $this->module, array(
                    "product_translation" => GetTranslation(
                        "product-group-" . ProductGroup::GetProductGroupCodeByID($this->GetProperty("group_id")),
                        "product"
                    )
                ));

                return false;
            }
        }

        $user = new User();
        $user->LoadBySession();

        $query = "UPDATE receipt SET 
					group_id=" . $this->GetIntProperty("group_id") . ",
					document_guid=" . $this->GetPropertyForSQL("document_guid") . ",
					document_date=" . Connection::GetSQLDateTime($this->GetProperty("document_date")) . ",
					document_date_from=" . Connection::GetSQLDate($this->GetProperty("document_date_from")) . ",
					document_date_to=" . Connection::GetSQLDate($this->GetProperty("document_date_to")) . ", 
					status=" . $this->GetPropertyForSQL("status") . ", 
					updated=" . Connection::GetSQLString(GetCurrentDateTime()) . ", 
					amount_approved=" . $this->GetPropertyForSQL("amount_approved") . ",
					real_amount_approved=" . $this->GetPropertyForSQL("real_amount_approved") . ",
					store_name=" . $this->GetPropertyForSQL("store_name") . ",
                    booked=" . $this->GetPropertyForSQL("booked") . ",
                    ref_number=" . $this->GetPropertyForSQL("ref_number") . ",
                    acc_system=" . $this->GetPropertyForSQL("acc_system") . ",
                    comment=" . $this->GetPropertyForSQL("comment") . ",
                    currency_id=" . $this->GetPropertyForSQL("currency_id") . ",
                    vat=" . $this->GetPropertyForSQL("vat") . ",
                    days_amount_under_16=" . $this->GetPropertyForSQL("days_amount_under_16") . ",
                    days_amount_over_16=" . $this->GetPropertyForSQL("days_amount_over_16") . ",
                    sets_of_goods=" . $this->GetPropertyForSQL("sets_of_goods") . ",
					receipt_from=" . $this->GetPropertyForSQL("receipt_from") .
            ($statusChanged ? ", status_user_id=" . $user->GetIntProperty("user_id") .
                ", status_updated=" . Connection::GetSQLString(GetCurrentDateTime()) .
                ", denial_reason=" . $this->GetPropertyForSQL("denial_reason") : "")
            . " WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("receipt_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        //check verification for payment calculation
        $this->CheckVerification();

        $specificProductGroup->ProcessAfterReceiptSave($this, $receiptBefore);

        if ($statusChanged && $this->GetProperty("status") == "approve_proposed") {
            $pushReceipt = new Receipt($this->module);
            $pushReceipt->LoadByID($this->GetProperty("receipt_id"));
            $pushReceipt->SendReceiptApproveProposedPushNotification();
        } elseif ($statusChanged && $this->GetProperty("status") == "denied") {
            $pushReceipt = new Receipt($this->module);
            $pushReceipt->LoadByID($this->GetProperty("receipt_id"));
            $pushReceipt->SendReceiptDeniedPushNotification();

            $this->SendReceiptCommentWithDenialReason();
            $pushReceipt->SendReceiptCommentPushNotification();
        }
        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Validates input data when trying to update receipt from admin panel. Also turns incorrect int/float properties into null.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    function ValidateUpdate($prevStatus)
    {
        //ad and recreation product groups set the amount_approved themselves
        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($this->GetProperty("group_id"));

        if (!in_array($productGroup->GetProperty("code"), array(PRODUCT_GROUP__AD, PRODUCT_GROUP__RECREATION))) {
            if (!$this->IsPropertySet("amount_approved") && $this->GetProperty("status") == "approve_proposed") {
                $this->AddError("receipt-empty-approved-value", $this->module);
            }
        }
        if (in_array($productGroup->GetProperty("code"), array(PRODUCT_GROUP__TRAVEL))) {
            if (!$this->GetProperty("vat")) {
                $this->SetProperty("vat", 19);
            }

            if ($this->GetProperty("booked") != "Y") {
                $this->SetProperty("booked", "N");
            }

            if ($this->GetProperty("booked") == "Y" && $prevStatus == "approved" && $this->GetProperty("status") != "approved") {
                $this->AddError("receipt-booked-error", $this->module);
            }

            if ($this->GetProperty("receipt_from") != "meal") {
                $this->RemoveProperty("days_amount_under_16");
                $this->RemoveProperty("days_amount_over_16");
            } else {
                if ($this->GetProperty("days_amount_under_16") == "") {
                    $this->SetProperty("days_amount_under_16", 0);
                }
                if ($this->GetProperty("days_amount_over_16") == "") {
                    $this->SetProperty("days_amount_over_16", 0);
                }
            }
        } else {
            $this->RemoveProperty("booked");
        }

        if ($productGroup->GetProperty("code") == PRODUCT_GROUP__BENEFIT_VOUCHER) {
            if ($this->GetProperty("sets_of_goods") == "alles für deine Ernährung") {
                $this->SetProperty("vat", 7);
            } else {
                $this->SetProperty("vat", 19);
            }
        }

        if (!$this->ValidateNotEmpty("document_date")) {
            if ($this->GetProperty("status") != "denied") {
                $this->AddError("receipt-add-document-date", $this->module);
            }
        } else {
            $employee = new Employee("company");
            $employee->LoadByID($this->GetProperty("employee_id"));

            $companyUnitPath2Root = CompanyUnitList::GetCompanyUnitPath2Root(
                $employee->GetProperty("company_unit_id"),
                true
            );
            $rootCompanyUnitID = end($companyUnitPath2Root);

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($rootCompanyUnitID);

            $currentMonthPayrollTime = strtotime(date("Y-m-" . intval($companyUnit->GetProperty("financial_statement_date")) . " 17:59:00")); //addition 18 hours for admin to approve receipt
            $currentMonthFirstDayTime = strtotime(date("Y-m-01"));
            $prevMonthFirstDayTime = $companyUnit->GetProperty("payroll_month") == "last_month"
                ? strtotime(date("Y-m-01", strtotime("- 1 month")))
                : $currentMonthFirstDayTime;
            $currentTime = time();
            $documentTime = strtotime($this->GetProperty("document_date"));
            if (
                ProductGroup::GetProductGroupCodeByID($this->GetProperty("group_id")) == PRODUCT_GROUP__FOOD &&
                ($this->GetProperty("status") != "denied")
                    && (($currentTime >= $currentMonthPayrollTime
                        && $documentTime < $currentMonthFirstDayTime)
                    || ($currentTime < $currentMonthPayrollTime
                        && $documentTime < $prevMonthFirstDayTime)
                )
            ) {
                $this->AddError("receipt-old", $this->module);
            }
            if (strtotime($this->GetProperty("document_date")) > strtotime(GetCurrentDateTime())) {
                $this->AddError("receipt-future-date", $this->module);
            }
        }

        $receiptNumberNotRequired = array(PRODUCT_GROUP__AD, PRODUCT_GROUP__RECREATION, PRODUCT_GROUP__TRAVEL);
        if (
            $this->GetProperty("document_guid") == null && !in_array(
                ProductGroup::GetProductGroupCodeByID($this->GetProperty("group_id")),
                $receiptNumberNotRequired
            ) && $this->GetProperty("status") == "approve_proposed"
        ) {
            $this->AddError("receipt-empty-document-guid", $this->module);
        }
        if ($this->GetProperty("document_guid") && $this->GetProperty("document_date")) {
            $stmt = GetStatement();
            $query = "SELECT receipt_id, legal_receipt_id, group_id, employee_id FROM receipt 
						WHERE document_guid=" . $this->GetPropertyForSQL("document_guid") . " 
							AND DATE(document_date)=" . Connection::GetSQLDate($this->GetProperty("document_date")) . "   
							AND receipt_id!=" . $this->GetIntProperty("receipt_id");
            $duplicateReceiptList = $stmt->FetchList($query);

            if (count($duplicateReceiptList) > 0) {
                foreach ($duplicateReceiptList as $duplicateReceipt) {
                    $receiptFileList = new ReceiptFileList($this->module);
                    $receiptFileList->LoadFileList($duplicateReceipt["receipt_id"]);
                    $productGroupTranslation = GetTranslation(
                        "product-group-" . ProductGroup::GetProductGroupCodeByID($duplicateReceipt["group_id"]),
                        'product'
                    );
                    $employeeName = Employee::GetNameByID($duplicateReceipt["employee_id"]);
                    $duplicateLegalReceiptIDs[] = "
                        <a class='duplicated-receipt-photo-link'
                            href='" . $receiptFileList->_items[0]["file_image_full_path"] . "'>" .
                            $duplicateReceipt["legal_receipt_id"] .
                        "</a>, " .
                        $productGroupTranslation . ", " . $employeeName;
                }

                $this->AddError(
                    "receipt-is-not-unique",
                    $this->module,
                    array("legal_receipt_ids" => implode(",<br/>", $duplicateLegalReceiptIDs))
                );
                $this->SetProperty("show_duplicate_receipt_photo", true);
            }
        }

        // To change the status, you need to check the availability of the contract (only for not vouchering services)
        $statusNeedCheckContract = ['approve_proposed'];
        $isNeedCheckContract = $prevStatus != $this->GetProperty('status') && in_array(
            $this->GetProperty('status'),
            $statusNeedCheckContract
        );
        $documentDate = $this->GetProperty('document_date');
        if ($isNeedCheckContract and !empty($documentDate)) {
            $productGroup = SpecificProductGroupFactory::Create($this->GetIntProperty('group_id'));
            $productGroupObj = new ProductGroup("product");
            $productGroupObj->LoadByID($this->GetIntProperty('group_id'));

            if ($productGroup !== null && $productGroupObj->GetProperty("voucher") == "N") {
                $productID = Product::GetProductIDByCode($productGroup->GetMainProductCode());

                $contract = new Contract('contract');
                $exists = $contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    intval($productID),
                    intval($this->GetProperty("employee_id")),
                    $documentDate
                );

                if (!$exists) {
                    $this->AddError('receipt-has-not-contract-date', $this->module);
                }
            }
        }

        // To change the status, you need to check the existing of the interruption contract
        $statusNeedCheck = ['approved', 'approve_proposed'];
        $productGroupNotCheck = [
            PRODUCT_GROUP__BENEFIT_VOUCHER,
            PRODUCT_GROUP__AD,
            PRODUCT_GROUP__GIFT,
            PRODUCT_GROUP__BONUS,
            PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT,
            PRODUCT_GROUP__BONUS_VOUCHER,
        ];
        $isNeedCheckContract = $prevStatus != $this->GetProperty('status') && in_array(
            $this->GetProperty('status'),
            $statusNeedCheck
        );
        $documentDate = $this->GetProperty('document_date');
        if ($isNeedCheckContract and !empty($documentDate)) {
            $productGroup = SpecificProductGroupFactory::Create($this->GetIntProperty('group_id'));

            if (
                $productGroup !== null && !in_array(
                    ProductGroup::GetProductGroupCodeByID($this->GetIntProperty('group_id')),
                    $productGroupNotCheck
                )
            ) {
                $contract = new Contract('contract');
                $exists = $contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION),
                    intval($this->GetProperty("employee_id")),
                    $documentDate
                );

                if ($exists) {
                    $this->AddError('employee-has-interruption-contract', $this->module);
                }
            }
        }

        $user = new User();
        $user->LoadBySession();
        if (
            $prevStatus != $this->GetProperty("status") &&
            $this->GetProperty("status") == "denied" &&
            $this->GetProperty("denial_reason") == "" &&
            !(
                $productGroup->GetProperty("code") == PRODUCT_GROUP__TRAVEL &&
                $user->Validate(["receipt" => null]) &&
                !$user->Validate(["root"])
            )
        ) {
            $this->AddError('receipt-has-no-denial-reason', $this->module);
        }

        if (($this->GetProperty("status") != $prevStatus) && ($this->GetProperty("status") != "denied")) {
            $this->SetProperty("denial_reason", "");
        }

        return !$this->HasErrors();
    }

    /**
     * Sets receipt "updated" field to current datetime
     *
     * @param int $receiptID receipt_id of receipt to be updated
     */
    public static function Touch($receiptID)
    {
        $stmt = GetStatement();
        $query = "UPDATE receipt SET updated=" . Connection::GetSQLString(GetCurrentDateTime()) . " WHERE receipt_id=" . intval($receiptID);
        $stmt->Execute($query);
    }

    /**
     * Get type of receipt (from shop or restaurant)
     *
     * @param int $receiptID receipt_id of receipt
     */
    public static function GetReceiptFromByID($receiptID)
    {
        $stmt = GetStatement();
        $query = "SELECT receipt_from FROM receipt WHERE receipt_id=" . intval($receiptID);

        return $stmt->FetchField($query);
    }

    /**
     * Get type of receipt (from shop or restaurant)
     *
     * @param string $property
     * @param int $receiptID receipt_id of receipt
     *
     * @return mixed
     */
    public static function GetReceiptFieldByID($property, $receiptID)
    {
        $stmt = GetStatement();

        return $stmt->FetchField("SELECT " . $property . " FROM receipt WHERE receipt_id=" . intval($receiptID));
    }

    /**
     * Get type of receipt (from shop or restaurant)
     *
     * @param int $receiptID receipt_id of receipt
     */
    public static function GetReceiptLegalID($receiptID)
    {
        $stmt = GetStatement();
        $query = "SELECT legal_receipt_id FROM receipt WHERE receipt_id=" . intval($receiptID);

        return $stmt->FetchField($query);
    }

    /**
     * Update fields for selected receipt.
     *
     * @param int $receiptID receipt_id of receipt field to be changed
     * @param string $field changing field
     * @param string $value new value of changind field
     * @param int $userID user_id for history
     *
     * @return bool true if field is changes successfully
     */
    public static function UpdateField($receiptID, $field, $value, $userID = 0)
    {
        $receiptID = intval($receiptID);
        if ($receiptID <= 0) {
            return;
        }

        $stmt = GetStatement();
        $query = "UPDATE receipt SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE receipt_id=" . $receiptID;

        if (!$stmt->Execute($query)) {
            return false;
        }

        self::Touch($receiptID);

        if ($userID == 0) {
            $user = new User();
            $user->LoadBySession();
            $userID = $user->GetIntProperty("user_id") > 0 ? $user->GetProperty("user_id") : BILLING_USER_ID;
        }

        if (($field == "document_date") && $value) {
            $value = date("Y-m-d", strtotime($value));
        }

        return !self::SaveHistoryRow($userID, $receiptID, $field, $value) ? false : true;
    }

    /**
     * Changes receipt's status to "approved" by mobile app user.
     * Current receipt status must be "approve_proposed".
     * Receipt must be loaded before this method will be called
     *
     * @return bool
     */
    public function ApproveByEmployee()
    {
        if ($this->GetProperty("status") != "approve_proposed") {
            return false;
        }

        if (
            ProductGroup::DoesEmployeeProductGroupHaveAdvancedSecurity(
                $this->GetProperty("group_id"),
                $this->GetProperty("employee_id"),
                $this->GetProperty("created")
            )
        ) {
            $receiptFileList = new ReceiptFileList($this->module);
            $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

            $signatureVeryfied = true;
            $signatureVerifySuccess = true;

            foreach ($receiptFileList->GetItems() as $receiptFile) {
                $signatureStatus = ReceiptFile::GetSignatureStatus($receiptFile["receipt_file_id"]);
                if (!$signatureStatus || $signatureStatus == "signature_create_error") {
                    if (
                        RabbitMQ::Send(
                            "signature_create",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"], "verify" => true)
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_create_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation",
                            "info"
                        );
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation error",
                            "error"
                        );
                    }
                    $signatureVeryfied = false;
                } elseif ($signatureStatus == "signature_created" || $signatureStatus == "signature_validate_error") {
                    if (
                        RabbitMQ::Send(
                            "signature_verify",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"])
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_verify_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "add rabbit mq task on verify", "info");
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on verify error",
                            "error"
                        );
                    }
                    $signatureVeryfied = false;
                } elseif ($signatureStatus != "signature_verify_success") {
                    $signatureVerifySuccess = false;
                }
            }

            if (!$signatureVeryfied) {
                $this->AddError("receipt-signature-not-verified", $this->module);

                return false;
            }

            if (!$signatureVerifySuccess) {
                $this->AddError("receipt-signature-verify-failed", $this->module);

                return false;
            }
        } else {
            if (!$this->ValidateHash("approve by employee")) {
                return false;
            }
        }

        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $stmt = GetStatement();
        $query = "UPDATE receipt 
					SET status='approved',
						status_user_id=" . $employee->GetIntProperty("user_id") . ", 
						status_updated=" . Connection::GetSQLString(GetCurrentDateTime()) . "
					WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
        if (!$stmt->Execute($query)) {
            return false;
        }

        if ($stmt->GetAffectedRows() > 0) {
            self::SaveHistoryRow(
                $employee->GetProperty("user_id"),
                $this->GetProperty("receipt_id"),
                "status",
                "approved"
            );
        }

        if ($this->GetProperty("group_id") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION)) {
            //update child count and material status for employee from receipt
            $receiptChildCount = Employee::GetPropertyHistoryValueEmployee(
                "child_count",
                $this->GetIntProperty("employee_id"),
                $this->GetProperty("created")
            );
            $receiptMaterialStatus = Employee::GetPropertyHistoryValueEmployee(
                "material_status",
                $this->GetIntProperty("employee_id"),
                $this->GetProperty("created")
            );
            Employee::SetEmployeeField(
                $employee->GetProperty("employee_id"),
                "child_count",
                $receiptChildCount["value"]
            );
            Employee::SetEmployeeField(
                $employee->GetProperty("employee_id"),
                "material_status",
                $receiptMaterialStatus["value"]
            );
            $employee->LoadByID($this->GetProperty("employee_id"));

            if (Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                    $this->GetIntProperty("employee_id"),
                    $this->GetProperty("document_date")
                ) == "N") {
                //create confirmation PDFs
                $confirmation = new RecreationConfirmation("agreements");
                $confirmation->LoadByCompanyUnitID(Employee::GetEmployeeField(
                    $this->GetProperty("employee_id"),
                    "company_unit_id"
                ));
                if ($confirmation->IsPropertySet('confirmation_id')) {
                    $companyUnit = new CompanyUnit("company");
                    $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

                    $stmt = GetStatement(DB_MAIN);
                    $query = "SELECT id FROM recreation_confirmation_employee ORDER BY id DESC";
                    $id = intval($stmt->FetchField($query)) + 1;

                    $fileName = "confirmation_" . $employee->GetProperty("company_unit_id") . "_" . $this->GetProperty("employee_id") . "_" . $id . ".pdf";
                    $confirmation->GenerateConfirmationToPdf(
                        $employee,
                        $companyUnit,
                        $fileName,
                        'send',
                        $this->GetProperty("receipt_id")
                    );
                }
            }

            //denied another receipts
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT receipt_id FROM receipt
                        WHERE employee_id=" . $this->GetIntProperty("employee_id") . "
                        AND group_id=" . $this->GetIntProperty("group_id") . "
                        AND status<>'denied'" . "
                        AND receipt_id<>" . $this->GetIntProperty("receipt_id");
            $receiptIDs = array_column($stmt->FetchList($query), "receipt_id");


            $query = "UPDATE receipt 
                        SET status='denied', denial_reason=" . Connection::GetSQLString(Config::GetConfigValue('receipt_autodeny_recreation')) . "
                        WHERE receipt_id IN (" . implode(",", $receiptIDs) . ")";

            if ($stmt->Execute($query)) {
                foreach ($receiptIDs as $receiptID) {
                    self::SaveHistoryRow(SERVICE_USER_ID, $receiptID, "status", "denied");
                }
            } else {
                $this->AddError("sql-error");
            }
        }

        return true;
    }

    /**
     * Changes receipt's status to "denied" by mobile app user.
     * Current receipt status must be "approve_proposed".
     * Receipt must be loaded before this method will be called
     *
     * @return bool
     */
    public function DeniedByEmployee()
    {
        if (
            ProductGroup::DoesEmployeeProductGroupHaveAdvancedSecurity(
                $this->GetProperty("group_id"),
                $this->GetProperty("employee_id"),
                $this->GetProperty("created")
            )
        ) {
            $receiptFileList = new ReceiptFileList($this->module);
            $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

            $signatureVeryfied = true;
            $signatureVerifySuccess = true;

            foreach ($receiptFileList->GetItems() as $receiptFile) {
                $signatureStatus = ReceiptFile::GetSignatureStatus($receiptFile["receipt_file_id"]);
                if (!$signatureStatus || $signatureStatus == "signature_create_error") {
                    if (
                        RabbitMQ::Send(
                            "signature_create",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"], "verify" => true)
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_create_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation",
                            "info"
                        );
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation error",
                            "error"
                        );
                    }
                    $signatureVeryfied = false;
                } elseif ($signatureStatus == "signature_created" || $signatureStatus == "signature_validate_error") {
                    if (
                        RabbitMQ::Send(
                            "signature_verify",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"])
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_verify_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "add rabbit mq task on verify", "info");
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on verify error",
                            "error"
                        );
                    }
                    $signatureVeryfied = false;
                } elseif ($signatureStatus != "signature_verify_success") {
                    $signatureVerifySuccess = false;
                }
            }

            if (!$signatureVeryfied) {
                $this->AddError("receipt-signature-not-verified", $this->module);

                return false;
            }

            if (!$signatureVerifySuccess) {
                $this->AddError("receipt-signature-verify-failed", $this->module);

                return false;
            }
        } else {
            if (!$this->ValidateHash("denied by employee")) {
                return false;
            }
        }

        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $stmt = GetStatement();
        $query = "UPDATE receipt 
					SET status='denied',
						status_user_id=" . $employee->GetIntProperty("user_id") . ", 
						status_updated=" . Connection::GetSQLString(GetCurrentDateTime()) . "
					WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
        if (!$stmt->Execute($query)) {
            return false;
        }

        if ($stmt->GetAffectedRows() > 0) {
            Receipt::RemoveReceiptVoucherLinks($this->GetProperty("receipt_id"));

            self::UpdateField(
                $this->GetProperty("receipt_id"),
                'denial_reason',
                Connection::GetSQLString(Config::GetConfigValue('receipt_autodeny_by_employee'))
            );
            self::SaveHistoryRow(
                $employee->GetProperty("user_id"),
                $this->GetProperty("receipt_id"),
                "status",
                "denied"
            );

            if ($this->GetProperty("group_id") == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION)) {
                SpecificProductGroupRecreation::RemoveConfirmation($this);
            }

            $receiptComment = new ReceiptComment($this->module);
            $receiptComment->LoadFromArray([
                "receipt_id" => $this->GetProperty("receipt_id"),
                "user_id" => $employee->GetProperty("user_id"),
                "content" => GetTranslation("denied-receipt-chat-message", $this->module),
            ]);
            $receiptComment->Create();

            $query = "UPDATE receipt_comment SET read_by_employee='Y' 
                WHERE comment_id=".$receiptComment->GetIntProperty("comment_id");
            $stmt->Execute($query);
        }

        return true;
    }

    /**
     * Changes the status to "review" by admin when he opens the receipt
     * Current receipt status must be "new".
     * Receipt must be loaded before this method will be called
     *
     * @return bool
     */
    public function StartReview()
    {
        if ($this->GetProperty("status") != "new") {
            return;
        }

        $user = new User();
        $user->LoadBySession();

        $stmt = GetStatement();
        $query = "UPDATE receipt 
                    SET status='review',
                        status_user_id=" . $user->GetIntProperty("user_id") . ", 
                        status_updated=" . Connection::GetSQLString(GetCurrentDateTime()) . "
                    WHERE receipt_id=" . $this->GetIntProperty("receipt_id");
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        self::SaveHistoryRow(
            $user->GetProperty("user_id"),
            $this->GetProperty("receipt_id"),
            "status",
            "review"
        );
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $receiptID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListReceipt($property, $receiptID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name
					FROM receipt_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND receipt_id=" . intval($receiptID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        if (!$valueList) {
            return $valueList;
        }

        $userIDs = array_column($valueList, "user_id");

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id, 
						" . Connection::GetSQLDecryption("first_name") . " AS first_name, 
						" . Connection::GetSQLDecryption("last_name") . " AS last_name 
					FROM user_info 
					WHERE user_id IN(" . implode(",", $userIDs) . ")";
        $userInfo = $stmt->FetchIndexedList($query, "user_id");

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["first_name"] = $userInfo[$valueList[$i]['user_id']]['first_name'];
            $valueList[$i]["last_name"] = $userInfo[$valueList[$i]['user_id']]['last_name'];
            if ($property != "currency_id") {
                continue;
            }

            $valueList[$i]["value"] = Currency::GetDigitByID($valueList[$i]["value"]);
        }

        return $valueList;
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */

    public function SaveHistory($currentPropertyList, $user_id = null, $autodenied = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        if (is_null($user_id)) {
            $user = new User();
            $user->LoadBySession();
            $user_id = $user->GetProperty("user_id");
        }

        if ($autodenied) {
            $propertyList = array(
                "document_guid",
                "amount_approved",
                "real_amount_approved",
                "group_id",
                "document_date",
                "document_date_from",
                "document_date_to",
                "store_name",
                "receipt_from",
                "comment",
                "booked",
                "ref_number",
                "acc_system",
                "currency_id",
                "vat",
                "days_amount_under_16",
                "days_amount_over_16",
                "sets_of_goods"
            );
        } else {
            $propertyList = array(
                "document_guid",
                "amount_approved",
                "real_amount_approved",
                "status",
                "group_id",
                "document_date",
                "document_date_from",
                "document_date_to",
                "store_name",
                "receipt_from",
                "comment",
                "booked",
                "ref_number",
                "acc_system",
                "currency_id",
                "vat",
                "days_amount_under_16",
                "days_amount_over_16",
                "sets_of_goods"
            );
        }


        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }
        foreach ($propertyList as $key) {
            if (($key == "document_date") && $this->GetProperty($key)) {
                $this->SetProperty($key, date("Y-m-d", strtotime($this->GetProperty($key))));
            }

            if (!$this->IsPropertySet($key) || $currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            if (!self::SaveHistoryRow($user_id, $this->GetProperty("receipt_id"), $key, $this->GetProperty($key))) {
                return false;
            }
        }

        if ($autodenied) {
            if (
                !self::SaveHistoryRow(
                    SERVICE_USER_ID,
                    $this->GetProperty("receipt_id"),
                    "status",
                    $this->GetProperty("status")
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Puts the record about changed field to database
     *
     * @param int $userID user_id of user who makes the changes
     * @param int $receiptID receipt_id of changed receipt
     * @param string $key key of changed property
     * @param string $value new value
     *
     * @return bool|NULL true if inserted successfully or false|null otherwise
     */
    public static function SaveHistoryRow($userID, $receiptID, $key, $value)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO receipt_history (receipt_id, property_name, value, created, user_id)
					VALUES (
						" . intval($receiptID) . ",
						" . Connection::GetSQLString($key) . ",
						" . Connection::GetSQLString($value) . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . intval($userID) . ")
					RETURNING value_id";

        return $stmt->Execute($query);
    }

    /**
     * Returns array of current value of properties
     *
     * @param int $id receipt_id which values is searched
     *
     * @return array
     */
    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT document_guid, amount_approved, real_amount_approved, status, group_id, DATE(document_date) AS document_date, document_date_from, document_date_to, store_name,
                    receipt_from, comment, booked, acc_system, ref_number, currency_id, vat, days_amount_under_16, days_amount_over_16, sets_of_goods
					FROM receipt 
					WHERE receipt_id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Sends push notification to employee owning receipt which status is changed to approve_proposed.
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptApproveProposedPushNotification()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "receipt_id" => $this->GetProperty("receipt_id"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "created" => date("d.m.Y", strtotime($this->GetProperty("created")))
        );

        $template = Config::GetConfigValue("push_receipt_processed");
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $data = array(
            FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_view",
            "receipt_id" => $this->GetProperty("receipt_id")
        );

        Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, $data);
    }

    /**
     * Sends push notification to employee owning receipt which status is changed to denied.
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptDeniedPushNotification()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "receipt_id" => $this->GetProperty("receipt_id"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "created" => date("d.m.Y", strtotime($this->GetProperty("created")))
        );

        $template = Config::GetConfigValue("push_receipt_denied");
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $data = array(
            FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_view",
            "receipt_id" => $this->GetProperty("receipt_id")
        );

        Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, $data);
    }

    /**
     * Sends push notification to employee owning processed receipt after X days.
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptProcessedRemindPushNotification()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "receipt_id" => $this->GetProperty("receipt_id"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "created" => date("d.m.Y", strtotime($this->GetProperty("created")))
        );

        $template = Config::GetConfigValue("push_receipt_processed_remind");
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $data = array(
            FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_view",
            "receipt_id" => $this->GetProperty("receipt_id")
        );

        return Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, $data, array());
    }

    /**
     * Sends push notification to employee owning processed receipt in X days before payroll day of his company
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptPayrollPushNotification()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "receipt_id" => $this->GetProperty("receipt_id"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "created" => date("d.m.Y", strtotime($this->GetProperty("created")))
        );

        $template = Config::GetConfigValue("push_receipt_payroll");
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $data = array(
            FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_view",
            "receipt_id" => $this->GetProperty("receipt_id")
        );

        return Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, $data, array());
    }

    /**
     * Sends push notification to employee owning approved benefit receipt that will expire soon
     * Object should be loaded by id before this method will be called
     */
    public function SendExpiringBenefitReceiptNotification($type)
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        if ($type == 1) {
            //last month of receipt period
            $text = Config::GetConfigValue("push_receipt_benefit_1");
        } elseif ($type == 2 || $type == 3) {
            //last day of first/second month after receipt period
            $text = Config::GetConfigValue("push_receipt_benefit_2_3");
        } elseif ($type == 4) {
            //first day of third month after receipt period
            $replacements = array(
                "max_monthly" => Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__BENEFIT__MAIN__EMPLOYER_GRANT,
                    $employee->GetProperty("employee_id"),
                    GetCurrentDate()
                )
            );

            $template = Config::GetConfigValue("push_receipt_benefit_4");
            $text = GetLanguage()->ReplacePairs($template, $replacements);
        }

        return Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, array(), array());
    }

    /**
     * Sends push notification to employee owning processed receipt in X days before payroll day of his company
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptCommentPushNotification()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "receipt_id" => $this->GetProperty("receipt_id"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "created" => date("d.m.Y", strtotime($this->GetProperty("created")))
        );

        $template = Config::GetConfigValue("push_receipt_comment_new");
        $text = GetLanguage()->ReplacePairs($template, $replacements);
        $data = array(
            FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "receipt_comment_list",
            "receipt_id" => $this->GetProperty("receipt_id")
        );

        Employee::SendPushNotification($this->GetProperty("employee_id"), null, $text, $data);
    }

    /*
     * Really totally remove receipt record from DB
     * */
    public function RemoveReceiptData()
    {
        $stmt = GetStatement(DB_MAIN);

        $stmt->Execute("DELETE FROM receipt_line WHERE receipt_id=" . $this->GetIntProperty("receipt_id"));
        $stmt->Execute("DELETE FROM receipt_comment WHERE receipt_id=" . $this->GetIntProperty("receipt_id"));

        $receiptFileList = new ReceiptFileList($this->module);
        $receiptFileList->LoadFileList($this->GetIntProperty("receipt_id"));
        if ($receiptFileList->GetCountItems() > 0) {
            $receiptFileList->Remove(array_column($receiptFileList->GetItems(), "receipt_file_id"));
        }

        $query = "DELETE FROM receipt WHERE receipt_id=" . $this->GetIntProperty("receipt_id");

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");
        }

        $query = "DELETE FROM voucher_receipt WHERE receipt_id=" . $this->GetIntProperty("receipt_id");

        if ($stmt->Execute($query)) {
            return true;
        } else {
            $this->AddError("sql-error");
        }

        return false;
    }

    /**
     * Sends comment with chosen denial reason for denied receipt
     * Object should be loaded by id before this method will be called
     * Object should have denial_reason property
     */
    public function SendReceiptCommentWithDenialReason()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id"),
            "denial_reason" => $this->GetProperty("denial_reason")
        );

        $template = Config::GetConfigValue("receipt_denial_comment");
        $text = GetLanguage()->ReplacePairs($template, $replacements);

        $user = new User();
        $user->LoadBySession();

        $receiptComment = new ReceiptComment($this->module);
        $receiptComment->SetProperty("receipt_id", $this->GetProperty("receipt_id"));
        $receiptComment->SetProperty("user_id", $user->GetProperty("user_id"));
        $receiptComment->SetProperty("content", $text);
        $receiptComment->SetProperty("read_by_admin", "Y");
        $receiptComment->Create();
    }

    /**
     * Sends comment for approved receipt which use less than 1 unit
     * Object should be loaded by id before this method will be called
     */
    public function SendReceiptCommentApprovedLessThanUnit()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name"),
            "legal_receipt_id" => $this->GetProperty("legal_receipt_id")
        );

        $template = Config::GetConfigValue("message_for_approved_less_than_unit");
        $text = GetLanguage()->ReplacePairs($template, $replacements);

        $user = new User();
        $user->LoadBySession();

        $receiptComment = new ReceiptComment($this->module);
        $receiptComment->SetProperty("receipt_id", $this->GetProperty("receipt_id"));
        $receiptComment->SetProperty("user_id", SERVICE_USER_ID);
        $receiptComment->SetProperty("content", $text);
        $receiptComment->SetProperty("read_by_admin", "Y");
        $receiptComment->Create();
    }

    /**
     * Get and parce denial reason list from config
     */
    public static function GetDenialReasonList()
    {
        $callback = static function ($str) {
            $str = trim($str);

            //$str = utf8_encode($str);
            return $str;
        };

        $denialReasonList = Config::GetConfigValue("receipt_denial_reason");
        $denialReasonList = preg_split("/\r\n|\r|\n/", $denialReasonList);
        $denialReasonList = array_map($callback, $denialReasonList);

        foreach ($denialReasonList as $key => $denialReason) {
            $denialReasonList[$key] = array("Reason" => $denialReason);
        }

        return $denialReasonList;
    }

    /**
     * Get and parce sets of goods list from config
     *
     * @param Receipt $receipt
     * @param bool $forApi
     *
     * @return array
     */
    public static function GetSetsOfGoodsList($receipt, $forApi = false)
    {
        $groupCode = ProductGroup::GetProductGroupCodeByID($receipt->GetProperty("group_id"));
        if ($receipt->GetProperty("receipt_id") > 0) {
            $selected = Receipt::GetReceiptFieldByID("sets_of_goods", $receipt->GetProperty("receipt_id"));
        } else {
            $selected = Voucher::GetDefaultVoucherReason(
                OPTION_LEVEL_EMPLOYEE,
                $receipt->GetProperty("employee_id"),
                $groupCode,
                GetCurrentDate()
            );
        }
        $setsOfGoods = Voucher::GetVoucherReasonList($selected, "voucher_sets_of_goods");

        foreach ($setsOfGoods as $key => $reason) {
            $setsOfGoods[$key] = ["set_of_goods" => $reason["Reason"], "key" => $key];
            if (empty($reason["Selected"]) || !$reason["Selected"]) {
                continue;
            }
            $setsOfGoods[$key]["selected"] = true;
            $selected = $reason["Reason"];
        }

        $voucherScenario = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$groupCode],
            $receipt->GetProperty("employee_id"),
            GetCurrentDate() //this option is not dependant on date and time (task #4009)
        );

        //always tell if there's no vouchers available
        $voucherList = VoucherList::GetAvailableVoucherListForReceipt($receipt);

        //if exchangeable scenario, all categories are visible
        if ($voucherScenario == "exchangeable") {
            //if it's for api, we need to remove first (= general) category
            if ($forApi) {
                unset($setsOfGoods[0]);
                $setsOfGoods = array_values($setsOfGoods);

                return ["sets_of_goods" => $setsOfGoods,
                    "selected" => $selected,
                    "show_voucher_category_select" => false,
                    "has_available_restrictions" => false,
                    "no_available_vouchers" => empty($voucherList)
                        ? GetTranslation("no-available-vouchers", "company")
                        : "",
                ];
            }
        }

        if (!$forApi) {
            return $setsOfGoods;
        }

        //otherwise we need to mark, which categories are available
        $reasonColumn = array_column($setsOfGoods, "set_of_goods");
        foreach ($voucherList as $voucher) {
            $key = array_search($voucher["reason"], $reasonColumn);
            if ($key == false) { //if key is zero, also skip it
                continue;
            }
            $setsOfGoods[$key]["available"] = true;
        }

        foreach ($setsOfGoods as $key => $reason) {
            if (!empty($reason["available"])) {
                continue;
            }
            $setsOfGoods[$key]["available"] = false;
        }

        $showSelect = false;
        if ($voucherScenario == "employee_flex") {
            $showSelect = true;
        }

        //we need to remove first (= general) category
        unset($setsOfGoods[0]);
        $setsOfGoods = array_values($setsOfGoods);

        return ["sets_of_goods" => $setsOfGoods,
            "selected" => $selected,
            "show_voucher_category_select" => $showSelect,
            "has_available_restrictions" => true,
            "no_available_vouchers" => empty($voucherList)
                ? GetTranslation("no-available-vouchers", "company")
                : "",
        ];
    }

    /**
     * Check if receipt's user has already achieved concerned limits and deny if has.
     *
     * @param string $statisticsDate date for which the limits are checking
     *
     * @return bool false if checking fails, true otherwise.
     */
    public function CheckLimits($statisticsDate)
    {
        $statisticsDate = $statisticsDate ?: GetCurrentDate();

        $employee = new Employee($this->module);
        $employee->LoadByID($this->GetIntProperty("employee_id"));
        $statData = Statistics::GetStatistics($employee, $statisticsDate);
        $productGroups = array_combine(
            array_column($statData['product_groups'], "group_id"),
            $statData['product_groups']
        );

        $excludeProductGroup = array(
            PRODUCT_GROUP__TRAVEL,
            PRODUCT_GROUP__AD,
            PRODUCT_GROUP__INTERNET,
            PRODUCT_GROUP__MOBILE
        );

        if (
            !isset($productGroups[$this->GetIntProperty("group_id")]) || in_array(
                $productGroups[$this->GetIntProperty("group_id")]["code"],
                $excludeProductGroup
            )
        ) {
            return true;
        }

        $statData = $productGroups[$this->GetIntProperty("group_id")];
        $groupTitle = GetTranslation("product-group-" . $statData['code'], "product");
        $result = true;
        $denialReason = "";

        if (round($statData['available_month'] - $statData['approved_month'], 2) <= 0) {
            $result = false;
            $month = GetTranslation("date-" . date_create($statisticsDate)->format("F"));
            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name"),
                "service" => $groupTitle,
                "month" => $month
            );
            $template = Config::GetConfigValue("message_automatic_denyal_month");
            $message = GetLanguage()->ReplacePairs($template, $replacements);
            $denialReason = Config::GetConfigValue('receipt_autodeny_month_limit');
        } elseif (round($statData['available_year'] - $statData['approved_year'], 2) <= 0) {
            $result = false;
            $year = date_create($statisticsDate)->format("Y");
            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name"),
                "service" => $groupTitle,
                "year" => $year
            );
            $template = Config::GetConfigValue("message_automatic_denyal_year");
            $message = GetLanguage()->ReplacePairs($template, $replacements);
            $denialReason = Config::GetConfigValue('receipt_autodeny_year_limit');
        }

        if (!$result) {
            Receipt::UpdateField($this->GetIntProperty("receipt_id"), "status", "denied");
            Receipt::UpdateField($this->GetIntProperty("receipt_id"), "automatic_processed", "Y");
            Receipt::UpdateField(
                $this->GetIntProperty("receipt_id"),
                'denial_reason',
                Connection::GetSQLString($denialReason)
            );

            $receiptComment = new ReceiptComment($this->module);
            $receiptComment->SetProperty("receipt_id", $this->GetProperty("receipt_id"));
            $receiptComment->SetProperty("user_id", SERVICE_USER_ID);
            $receiptComment->SetProperty("content", $message);
            $receiptComment->SetProperty("read_by_admin", "Y");
            $receiptComment->Create();
        }

        return $result;
    }

    /**
     * Validates user receipt Role
     *
     * @param int $receiptID receipt_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($receiptID, $userID = null)
    {
        if (!$receiptID) {
            return true;
        }

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptID);

        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $permissionReceipt = "receipt";
        $permissionTaxAuditor = "tax_auditor";
        $permissionService = "service";
        $permissionPayroll = "payroll";
        $permissionEmployee = "employee";

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        $productGroupIDs = array();
        if ($user->Validate(array($permissionService => null))) {
            $productGroupIDs = $user->GetPermissionLinkIDs($permissionService);
        }

        if (count($productGroupIDs) > 0 && !in_array($receipt->GetProperty("group_id"), $productGroupIDs)) {
            return false;
        }

        if ($user->Validate(array($permissionReceipt))) {
            return true;
        }

        if ($user->Validate(array($permissionTaxAuditor))) {
            $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true);
            $forbiddenProductGroupIDs = array_column($voucherProductGroupList, "group_id");

            return in_array($receipt->GetProperty("group_id"), $forbiddenProductGroupIDs) ? false : true;
        }

        if ($user->Validate(array($permissionPayroll => null, $permissionEmployee => null), "and")) {
            $companyUnitIDs = $user->GetPermissionLinkIDs($permissionEmployee);
            $companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs($permissionEmployee));
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);

            return count($companyUnitIDs) != 0;
        } else {
            $permissionName = $permissionReceipt;
            if ($user->Validate(array($permissionTaxAuditor => null))) {
                $permissionName = $permissionTaxAuditor;
            }

            $companyUnitIDs = $user->GetPermissionLinkIDs($permissionName);
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) == 0) {
                return false;
            }
        }

        return in_array($employee->GetIntProperty("company_unit_id"), $companyUnitIDs) ? true : false;
    }

    /**
     * Validates employee advanced security signature before update receipt
     *
     * @return bool
     */
    private function ValidateAdvancedSecurity()
    {
        if (
            ProductGroup::DoesEmployeeProductGroupHaveAdvancedSecurity(
                $this->GetProperty("group_id"),
                $this->GetProperty("employee_id"),
                $this->GetProperty("created")
            )
        ) {
            $receiptFileList = new ReceiptFileList($this->module);
            $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

            $signatureExist = true;
            foreach ($receiptFileList->GetItems() as $receiptFile) {
                $signatureStatus = ReceiptFile::GetSignatureStatus($receiptFile["receipt_file_id"]);
                if (!$signatureStatus || $signatureStatus == "signature_create_error") {
                    if (
                        RabbitMQ::Send(
                            "signature_create",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"], "verify" => false)
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_create_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation",
                            "info"
                        );
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on creation error",
                            "info"
                        );
                    }
                    $signatureExist = false;
                } elseif ($signatureStatus == "signature_create_started") {
                    $signatureExist = false;
                } elseif ($signatureStatus == "signature_created" || $signatureStatus == "signature_verify_error" || $signatureStatus == "signature_verify_failed") {
                    if (
                        RabbitMQ::Send(
                            "signature_verify",
                            array("receipt_file_id" => $receiptFile["receipt_file_id"])
                        )
                    ) {
                        ReceiptFile::SetSignatureStatus($receiptFile["receipt_file_id"], "signature_verify_started");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "info");
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "add rabbit mq task on verify", "info");
                    } else {
                        ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "--------", "error");
                        ReceiptFile::WriteLog(
                            $receiptFile["receipt_file_id"],
                            "add rabbit mq task on verify error",
                            "info"
                        );
                        $signatureExist = false;
                    }
                }
            }

            if (!$signatureExist) {
                $this->AddError("receipt-signature-not-exist", $this->module);

                return false;
            }

            return true;
        }

        return $this->ValidateHash("approve_proposed setting");
    }

    /**
     * Compare receipt files hash from db and from storage (file hash)
     */
    private function ValidateHash($logEvent)
    {
        if (IsLocalEnvironment()) {
            return true;
        }

        $receiptFileList = new ReceiptFileList($this->module);
        $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($this->GetProperty("receipt_id"));

        $specificProductGrpup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileStorage = GetFileStorage($container);

        foreach ($receiptFileList->GetItems() as $receiptFile) {
            $hash = hash(
                "sha256",
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"])
            );

            ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "validating file hash on " . $logEvent, "info");
            ReceiptFile::WriteLog(
                $receiptFile["receipt_file_id"],
                "file hash stored in database: " . $receiptFile["hash"],
                "info"
            );
            ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "actual file hash: " . $hash, "info");

            if ($hash != $receiptFile["hash"]) {
                $this->AddError("receipt-hash-not-equals", $this->module);
                $this->IntegrityCheckDeny();

                ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "hash comparsion result: failure", "info");

                return false;
            }
            ReceiptFile::WriteLog($receiptFile["receipt_file_id"], "hash comparsion result: success", "info");
        }

        return true;
    }

    /**
     * Autodeny if something wrong (hashes not equal)
     */
    public function IntegrityCheckDeny()
    {
        $employee = new Employee("company");
        $employee->LoadByID($this->GetProperty("employee_id"));

        $replacements = array(
            "salutation" => $employee->GetProperty("salutation"),
            "first_name" => $employee->GetProperty("first_name"),
            "last_name" => $employee->GetProperty("last_name")
        );

        $template = Config::GetConfigValue("message_automatic_denyal_integrity_check");
        $message = GetLanguage()->ReplacePairs($template, $replacements);
        $denialReason = Config::GetConfigValue('receipt_autodeny_integrity_check');

        Receipt::UpdateField($this->GetProperty("receipt_id"), "status", "denied");
        Receipt::UpdateField($this->GetProperty("receipt_id"), "automatic_processed", "Y");
        Receipt::UpdateField(
            $this->GetProperty("receipt_id"),
            'denial_reason',
            Connection::GetSQLString($denialReason)
        );

        $receiptComment = new ReceiptComment("receipt");
        $receiptComment->SetProperty("receipt_id", $this->GetProperty("receipt_id"));
        $receiptComment->SetProperty("user_id", SERVICE_USER_ID);
        $receiptComment->SetProperty("content", $message);
        $receiptComment->SetProperty("read_by_admin", "Y");
        $receiptComment->Create();

        $this->SetProperty("status", "denied");
        $this->SetProperty("automatic_processed", "Y");
        $this->SetProperty("denial_reason", Connection::GetSQLString($denialReason));
    }

    /**
     * Output (download) evidence pack. Object must be loaded from request before the method will be called.
     */
    public function CreateAndOutputEvidencePack()
    {
        //generate transfer note
        $fileSys = new FileSys();
        $transferNoteFilePath = $this->GenerateTransferNotePDF();

        $specificProductGrpup = SpecificProductGroupFactory::Create($this->GetProperty("group_id"));
        $container = $specificProductGrpup->GetContainer();

        $fileStorage = GetFileStorage($container);
        $fileName = "evidence_" . $this->GetProperty("receipt_id") . ".zip";
        $filePath = PROJECT_DIR . "var/log/" . $fileName;

        //create zip archive
        $z = new ZipArchive();

        $z->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        //load receipt file list for receipt
        $receiptFileList = new ReceiptFileList($this->module);
        $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

        //add image, signature, siganture report to archive for each receipt file
        foreach ($receiptFileList->GetItems() as $receiptFile) {
            $z->addFromString(
                $receiptFile["file_image"],
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"])
            );
            if ($receiptFile["signature_file"] != "") {
                $z->addFromString(
                    $receiptFile["signature_file"],
                    $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_file"])
                );
            }
            if (!$receiptFile["signature_report_file"]) {
                continue;
            }

            $z->addFromString(
                $receiptFile["signature_report_file"],
                $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"])
            );
        }

        //add transfer note to archive
        $z->addFromString(
            "trebono_transfervermerk_" . $this->GetProperty("receipt_id") . ".pdf",
            $fileSys->GetFileContent($transferNoteFilePath)
        );

        $z->close();

        //remove transfer note
        $fileSys->Remove($transferNoteFilePath);

        if ($fileSys->FileExists($filePath)) {
            //output zip archive
            header("Content-Type: application/zip");
            header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
            header("Cache-Control: public, must-revalidate, max-age=0");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            echo $fileSys->GetFileContent($filePath);

            //remove zip archive
            $fileSys->Remove($filePath);
            exit();
        }

        Send404();
    }

    /**
     * Generate pdf transfer note pdf and store it in var/log
     * Object must be loaded by id before this method will be called
     *
     * @return string file path
     */
    public function GenerateTransferNotePDF()
    {
        $tmpPath = PROJECT_DIR . "var/log/";
        $fileName = "trebono_transfervermerk_" . $this->GetProperty("receipt_id");

        //load receipt, product group, employee, org guideline, company unit and signature report data
        //receipt
        $receipt = new Receipt($this->module);
        $receipt->LoadByID($this->GetProperty("receipt_id"));

        $receiptArray = array();
        foreach ($receipt->GetProperties() as $key => $property) {
            $receiptArray["receipt_" . $key] = $property;
        }

        //load receipt file list for receipt
        $receiptFileList = new ReceiptFileList($this->module);
        $receiptFileList->LoadFileList($this->GetProperty("receipt_id"));

        $receiptFileListArray = $receiptFileList->GetItems();

        //product group
        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($receipt->GetProperty("group_id"));

        $productGroupArray = array();
        foreach ($productGroup->GetProperties() as $key => $property) {
            if ($key == "title" || $key == "title_translation") {
                $property = GetTranslation(
                    "product-group-" . $productGroup->GetProperty("code"),
                    "product",
                    null,
                    "de"
                );
            }

            $productGroupArray["product_group_" . $key] = $property;
        }

        //employee
        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $employeeArray = array();
        foreach ($employee->GetProperties() as $key => $property) {
            $employeeArray["employee_" . $key] = $property;
        }

        //organizational guideline
        $orgGuidelineEmployee = Employee::GetPropertyHistoryValueEmployee(
            "org_guideline_version",
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("created")
        );
        $orgGuidelineConfig = false;
        if ($orgGuidelineEmployee) {
            $orgGuidelineConfig = Config::GetConfigHistoryValue($orgGuidelineEmployee["value"]);
        }
        //company unit
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

        $companyUnitArray = array();
        foreach ($companyUnit->GetProperties() as $key => $property) {
            $companyUnitArray["company_unit_" . $key] = $property;
        }

        foreach ($receiptFileListArray as $key => $receiptFile) {
            $specificProductGrpup = SpecificProductGroupFactory::Create($this->GetProperty("group_id"));
            $container = $specificProductGrpup->GetContainer();

            //signature report
            $fileStorage = GetFileStorage($container);
            $reportHash = "";
            $checkTime = "";
            if ($receiptFile["signature_report_file"] != "" && $fileStorage->FileExists(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"])) {
                try {
                    $reportXml = simplexml_load_string($fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"]));
                    if (isset($reportXml->document->hash)) {
                        $reportHash = array((string)$reportXml->document->hash)[0];
                    }
                    if (isset($reportXml->summary->checktime)) {
                        $checkTime = array((string)$reportXml->summary->checktime)[0];
                    }
                } catch (Exception $e) {
                }
            } else {
                $reportHash = strtoupper(hash(
                    "sha256",
                    $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"])
                ));
                $checkTime = $receipt->GetProperty("status_updated");
            }

            $compareHashResult = strtoupper($receiptFile["hash"]) == $reportHash ? "success" : "fail";

            $receiptFileListArray[$key]["hash"] = strtoupper($receiptFile["hash"]);

            $receiptFileListArray[$key]["signature_report_hash"] = $reportHash;
            $receiptFileListArray[$key]["signature_report_date"] = $checkTime;
            $receiptFileListArray[$key]["compare_hash_result"] = $compareHashResult;
        }

        //receipt comment list
        $receiptCommentList = new ReceiptCommentList($this->module);
        $receiptCommentList->LoadCommentListForAdmin($receipt->GetProperty("receipt_id"));

        //create pdf template
        $popupPage = new PopupPage($this->module);
        $content = $popupPage->Load("transfer_note_pdf.html");
        $content->SetVar("Module", $this->module);

        //load all data to content
        $content->LoadFromObject($this);

        $content->LoadFromArray($receiptArray);
        $content->LoadFromArray($employeeArray);
        $content->LoadFromArray($companyUnitArray);
        $content->LoadFromArray($productGroupArray);
        $content->LoadFromObjectList("ReceiptCommentList", $receiptCommentList);

        $replacements = array(
            "org_guideline_version" => $orgGuidelineEmployee["value"] ?? null,
            "org_guideline_accept_date" => ($orgGuidelineEmployee ? date(
                "d.m.Y H:i:s",
                strtotime($orgGuidelineEmployee["created"])
            ) : null),
            "org_guideline_create_date" => ($orgGuidelineConfig ? date(
                "d.m.Y H:i:s",
                strtotime($orgGuidelineConfig["date_from"])
            ) : null),
            "org_guideline_url" => GetUrlPrefix() . ADMIN_FOLDER . "/module.php?load=receipt&Section=receipt&config_version_id=" . ($orgGuidelineEmployee["value"] ?? null)
        );
        $content->SetVar(
            "org_guideline_block_html",
            GetTranslation("receipt-file-transfer-note-org-guideline-html", $this->module, $replacements)
        );

        $content->SetLoop("ReceiptFileList", $receiptFileListArray);

        $html = $popupPage->Grab($content);

        //create pdf
        $pdf = new mPDF("utf-8", "A4", "11", "dejavusans", 15, 15, 40, 16, 4, 6);

        $pdf->PDFA = true;
        $pdf->PDFAauto = true;

        $css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/transfer_note_pdf_style.css");

        $pdf->WriteHTML($css, 1);

        $pdf->writeHTML($html, 2);

        //$pdf->Output($fileName, "I");die();

        $pdf->Output($tmpPath . $fileName, "F");

        return $tmpPath . $fileName;
    }

    public static function GetReceiptVoucherList($receiptID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT voucher_id, amount FROM voucher_receipt WHERE receipt_id=" . Connection::GetSQLString($receiptID);

        return $stmt->FetchList($query);
    }

    public static function RemoveReceiptVoucherLinks($receiptID)
    {
        $receiptVoucherIDs = array_column(Receipt::GetReceiptVoucherList($receiptID), "voucher_id");
        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptID);
        $groupCode = ProductGroup::GetProductGroupCodeByID($receipt->GetProperty("group_id"));

        if (count($receiptVoucherIDs) > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "DELETE FROM voucher_receipt WHERE receipt_id=" . Connection::GetSQLString($receiptID);
            if (!$stmt->Execute($query)) {
                return false;
            }

            if ($groupCode == PRODUCT_GROUP__BENEFIT_VOUCHER) {
                foreach ($receiptVoucherIDs as $receiptVoucherID) {
                    $voucherReceipts = Voucher::GetVoucherReceiptLinks($receiptVoucherID);
                    if (count($voucherReceipts) == 0) {
                        $setsOfGood = Voucher::GetVoucherReasonList(
                            Voucher::GetDefaultVoucherReason(
                                OPTION_LEVEL_EMPLOYEE,
                                $receipt->GetProperty("employee_id"),
                                $groupCode,
                                $receipt->GetProperty("document_date")
                            ),
                            "voucher_sets_of_goods"
                        );

                        foreach ($setsOfGood as $reason) {
                            if ($reason["Selected"]) {
                                $defaultReason = $reason["Reason"];
                            }
                        }
                        Voucher::SetVoucherReason($receiptVoucherID, $defaultReason);
                    }

                    $setsOfGood = Voucher::GetVoucherReasonList(true, "voucher_sets_of_goods");
                    Voucher::SetVoucherReason($receiptVoucherID, $setsOfGood[0]["Reason"]);
                }
            }
        }

        return true;
    }

    public static function GetVoucherReceiptLinks($receiptID)
    {
        if (intval($receiptID) > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT voucher_id, amount, created FROM voucher_receipt WHERE receipt_id=" . Connection::GetSQLString($receiptID);

            return $stmt->FetchList($query);
        }

        return array();
    }

    /**
     * Check receipt verification for calculation processing dashboard. If it is true, operation save to receipt_verification_history table
     * Object must be loaded by id before this method will be called
     *
     * @return true|false
     */
    public function CheckVerification()
    {
        $savedStatus = $this->GetProperty("status");

        //saved status must not be "new"
        if ($savedStatus != "new") {
            $savedTime = date_create()->format("Y-m-d H:i:s.u");

            $user = new User();
            $user->LoadBySession();

            $stm = GetStatement(DB_CONTROL);

            $query = "SELECT MIN(date) as date FROM operation
                        WHERE code = 'receipt_id' AND
                            user_id=" . $user->GetIntProperty("user_id") . " AND
                            object_id=" . $this->GetIntProperty("legal_receipt_id");

            $openingTime = $stm->FetchField($query);

            if ($openingTime) {
                $statusValueList = Receipt::GetPropertyValueListReceipt("status", $this->GetIntProperty("receipt_id"));

                $openingTimeWithoutMillisec = substr($openingTime, 0, 19);

                foreach ($statusValueList as $statusValue) {
                    if (strtotime($statusValue["created"]) < strtotime($openingTimeWithoutMillisec)) {
                        $openingStatus = $statusValue["value"];
                        break;
                    }
                }

                //opening status must be "new"
                if ($openingStatus == "new") {
                    $query = "SELECT operation_id FROM operation
                            WHERE user_id=" . $user->GetIntProperty("user_id") . " AND
                                date>" . Connection::GetSQLString($openingTime) . " AND
                                date<" . Connection::GetSQLString($savedTime);

                    $operations = $stm->FetchList($query);

                    //user must not have operations between receipt opening and saving
                    if (is_array($operations) && count($operations) == 0) {
                        $this->SaveVerification($openingStatus, $savedStatus, $openingTime, $savedTime);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Save receipt verification for calculation processing dashboard. Object must be loaded from request before the method will be called.
     *
     * @param string $openingStatus receipt status, when it was opened
     * @param string $savedStatus receipt status, when it was saved
     * @param string $openingTime date-time, when receipt was opened
     * @param string $savedTime date-time, when receipt was saved
     * @param int $userID verifier user_id
     *
     * @return bool true if record is created successfully or false on failure
     */
    public function SaveVerification($openingStatus, $savedStatus, $openingTime, $savedTime, $userID = null)
    {
        if (!$userID) {
            $user = new User();
            $user = new User();
            $user->LoadBySession();
            $userID = $user->GetIntProperty("user_id");
        }

        $amount = Config::GetConfigValueByDate("receipt_verificator_payment_" . $savedStatus, $openingTime);

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO receipt_verification_history (receipt_id, user_id, opening_status, saved_status, amount, opening_receipt_at, saved_receipt_at, created_at) VALUES (
						" . $this->GetIntProperty("receipt_id") . ",
						" . $userID . ",
						" . Connection::GetSQLString($openingStatus) . ",
						" . Connection::GetSQLString($savedStatus) . ",
						" . $amount . ",						
						" . Connection::GetSQLString($openingTime) . ",
						" . Connection::GetSQLString($savedTime) . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ")";

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }
}
