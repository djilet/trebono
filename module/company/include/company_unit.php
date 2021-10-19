<?php

class CompanyUnit extends LocalObject
{
    private $_acceptMimeTypes = array(
        'image/png',
        'image/x-png'
    );
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of company unit properties to be loaded instantly
     */
    public function CompanyUnit($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = [
            "app_logo" => LoadImageConfig('app_logo_image', "company", COMPANY_APP_LOGO_IMAGE),
            "app_logo_mini" => LoadImageConfig('app_logo_mini_image', $this->module, COMPANY_APP_LOGO_MINI_IMAGE),
            "voucher_logo" => LoadImageConfig('voucher_logo_image', $this->module, COMPANY_VOUCHER_LOGO_IMAGE),
        ];
    }

    /**
     * Loads company_unit by its company_unit_id
     *
     * @param int $id company_unit_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT u.company_unit_id, u.company_id, u.parent_unit_id, u.created,
						u.phone, u.email, u.zip_code, u.country, u.city, u.house, 
						u.client_id, u.vat_payer_id, u.comment, u.bank_details, u.bic, u.register, u.reg_email_text,
						u.tax_number, u.payment_type, u.invoice_date, u.financial_statement_date, u.payroll_month, u.datev_format, u.datev_encoding, u.customer_guid,
						u.acc_meal_value_tax_flat, u.acc_food_subsidy_tax_free, u.acc_gross_salary, u.acc_grant_of_materials,
						u.acc_internet_subsidy_tax, u.acc_mobile_subsidy_tax_free, u.acc_recreation_subsidy_tax_flat, u.acc_net_income,
						u.payment_method, u.tax_consultant, u.app_logo_image, u.app_logo_mini_image, u.agreement_enable, c.colorscheme, u.voucher_logo_image,
						u.acc_bonus_tax_flat, u.acc_transport_tax_free, u.acc_child_care_tax_free, u.acc_travel_tax_free,
						u.acc_daily_allowance, u.acc_gift, u.acc_corporate_health_management,
						u.acc_ticket, u.acc_accommodation, u.acc_hospitality,
						u.acc_parking, u.acc_other, u.acc_travel_costs, u.acc_creditor,
						u.creditor_number, u.sepa_service, u.sepa_voucher, u.sepa_service_date, u.sepa_voucher_date,
						" . Connection::GetSQLDecryption("u.title") . " AS title,
						" . Connection::GetSQLDecryption("u.street") . " AS street,
						" . Connection::GetSQLDecryption("u.iban") . " AS iban
					FROM company_unit AS u
						LEFT JOIN company AS c ON c.company_id=u.company_id
					WHERE u.company_unit_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("company_unit_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Loads company_unit by its company_unit_id. Object must be loaded from request before the method will be called.
     * Required properties are: title, customer_guid
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByTitleAndGuid()
    {
        $query = "SELECT u.company_unit_id, u.company_id, u.parent_unit_id, u.created,
						u.phone, u.email, u.zip_code, u.country, u.city, u.house,
						u.client_id, u.vat_payer_id, u.comment, u.bank_details, u.bic, u.register,
						u.tax_number, u.payment_type, u.invoice_date, u.financial_statement_date, u.payroll_month, u.datev_format, u.datev_encoding, u.customer_guid,
						u.acc_meal_value_tax_flat, u.acc_food_subsidy_tax_free, u.acc_gross_salary, u.acc_grant_of_materials,
						u.acc_internet_subsidy_tax, u.acc_mobile_subsidy_tax_free, u.acc_recreation_subsidy_tax_flat, u.acc_net_income,
						u.payment_method, u.tax_consultant, u.app_logo_image, u.app_logo_mini_image, u.agreement_enable, c.colorscheme, u.voucher_logo_image,
						u.acc_bonus_tax_flat, u.acc_transport_tax_free, u.acc_child_care_tax_free, u.acc_travel_tax_free,
						u.acc_daily_allowance, u.acc_gift, u.acc_corporate_health_management,
						u.acc_ticket, u.acc_accommodation, u.acc_hospitality,
						u.acc_parking, u.acc_other, u.acc_travel_costs, u.acc_creditor,
						u.sepa_service, u.sepa_voucher, u.sepa_service_date, u.sepa_voucher_date,
						" . Connection::GetSQLDecryption("u.title") . " AS title,
						" . Connection::GetSQLDecryption("u.street") . " AS street,
						" . Connection::GetSQLDecryption("u.iban") . " AS iban
					FROM company_unit AS u
						LEFT JOIN company AS c ON c.company_id=u.company_id
					WHERE u.customer_guid=" . $this->GetPropertyForSQL("customer_guid") . "
                        AND u.title::bytea=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("title"));

        $this->LoadFromSQL($query);
        if ($this->GetProperty("company_unit_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Creates or updates the company unit. Object must be loaded from request before the method will be called.
     * Required properties are: title, phone, email, zip_code, country, city, street, house, client_id, vat_payer_id, comment
     * If new company unit has no parent then its parent company entity will be created.
     * Also updates users permissions in case when company unit tree structure is changed.
     *
     * @return bool true if company unit is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }

        $stmt = GetStatement();

        if ($this->GetProperty("parent_unit_id")) {
            $query = "SELECT company_id FROM company_unit WHERE company_unit_id=" . $this->GetIntProperty("parent_unit_id");
            $this->SetProperty("company_id", $stmt->FetchField($query));
        }

        $this->SaveAppCompanyLogoImage($this->GetProperty("saved_app_logo_image"), "app_logo");
        $this->SaveAppCompanyLogoImage($this->GetProperty("saved_app_logo_mini_image"), "app_logo_mini");
        $this->SaveAppCompanyLogoImage($this->GetProperty("saved_voucher_logo_image"), "voucher_logo");

        $new = !boolval($this->GetProperty("company_unit_id"));

        if (!$new) {
            //save voucher service contracts existing before saving (check for master data)
            $contract = new Contract("product");
            $voucherProductExistBefore = array(
                PRODUCT__FOOD_VOUCHER__MAIN => false,
                PRODUCT__BENEFIT_VOUCHER__MAIN => false,
                PRODUCT__GIFT_VOUCHER__MAIN => false
            );
            foreach ($voucherProductExistBefore as $code => $existBefore) {
                if (
                    !$contract->LoadLatestActiveContract(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $this->GetProperty("company_unit_id"),
                        Product::GetProductIDByCode($code),
                        false
                    )
                ) {
                    continue;
                }

                $voucherProductExistBefore[$code] = true;
            }

            $this->SaveOptions($this->GetProperty('Product') ?? []);

            if ($this->HasErrors()) {
                return false;
            }

            $query = "SELECT archive FROM company_unit WHERE company_unit_id=" . $this->GetProperty("company_unit_id");
            $archiveValue = $stmt->FetchField($query);

            $baseMainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
            $baseContract = new Contract("product");
            $baseContract->LoadLatestActiveContract(
                OPTION_LEVEL_COMPANY_UNIT,
                $this->GetProperty("company_unit_id"),
                $baseMainProductID,
                false
            );
            //deactivate company only if base module end_date is the past date. otherwise it will be deactivated by cron on the next day after end_date
            if (
                isset($baseContract)
                && !empty($baseContract->GetProperty("end_date"))
                && strtotime($baseContract->GetProperty("end_date")) < strtotime(GetCurrentDate())
                && $archiveValue == "N"
            ) {
                $this->End($baseContract->GetProperty("end_date"));
            }

            //activate company if base module start_date is the past date (only manually)
            if (
                isset($baseContract)
                && (empty($baseContract->GetProperty("end_date"))
                    || strtotime($baseContract->GetProperty("end_date")) >= strtotime(GetCurrentDate())
                )
                && !empty($baseContract->GetProperty("start_date"))
                && strtotime($baseContract->GetProperty("start_date")) < strtotime(GetCurrentDate())
                && $archiveValue == "Y"
            ) {
                $companyList = new CompanyUnitList($this->module);
                $companyList->Activate([$this->GetProperty("company_unit_id")]);
            }

            if ($this->HasErrors()) {
                return false;
            }
        }
        if ($this->GetIntProperty("company_unit_id") > 0) {
            //save employee banking details before saving
            $companyUnitBeforeSave = new CompanyUnit($this->module);
            $companyUnitBeforeSave->LoadByID($this->GetProperty("company_unit_id"));
            $bicBeforeSave = trim($companyUnitBeforeSave->GetProperty("bic"));
            $bankNameBeforeSave = trim($companyUnitBeforeSave->GetProperty("bank_details"));
            $ibanBeforeSave = trim($companyUnitBeforeSave->GetProperty("iban"));
            $sepaServiceBeforeSave = trim($companyUnitBeforeSave->GetProperty("sepa_service"));
            $sepaVoucherBeforeSave = trim($companyUnitBeforeSave->GetProperty("sepa_voucher"));
            $sepaServiceDateBeforeSave = date(
                "Y-m-d",
                strtotime($companyUnitBeforeSave->GetProperty("sepa_service_date"))
            );
            $sepaVoucherDateBeforeSave = date(
                "Y-m-d",
                strtotime($companyUnitBeforeSave->GetProperty("sepa_voucher_date"))
            );

            $propertyList = [
                "title",
                "phone",
                "email",
                "zip_code",
                "country",
                "city",
                "street",
                "house",
                "client_id",
                "vat_payer_id",
                "comment",
                "bank_details",
                "iban",
                "bic",
                "register",
                "tax_number",
                "payment_type",
                "invoice_date",
                "financial_statement_date",
                "payroll_month",
                "datev_format",
                "datev_encoding",
                "acc_meal_value_tax_flat",
                "acc_food_subsidy_tax_free",
                "acc_gross_salary",
                "acc_grant_of_materials",
                "acc_internet_subsidy_tax",
                "acc_mobile_subsidy_tax_free",
                "acc_recreation_subsidy_tax_flat",
                "acc_net_income",
                "payment_method",
                "tax_consultant",
                "app_logo_image",
                "app_logo_mini_image",
                "agreement_enable",
                "voucher_logo_image",
                "acc_bonus_tax_flat",
                "acc_transport_tax_free",
                "acc_child_care_tax_free",
                "acc_travel_tax_free",
                "acc_daily_allowance",
                "acc_gift",
                "acc_corporate_health_management",
                "acc_ticket",
                "acc_accommodation",
                "acc_hospitality",
                "acc_parking",
                "acc_other",
                "acc_travel_costs",
                "acc_creditor",
                "sepa_service",
                "sepa_voucher",
                "sepa_service_date",
                "sepa_voucher_date",
                "reg_email_text",
            ];
            $query = "UPDATE company_unit SET
						parent_unit_id=" . $this->GetPropertyForSQL("parent_unit_id") . ",
						company_id=" . $this->GetPropertyForSQL("company_id");
            foreach ($propertyList as $property) {
                if (!$this->IsPropertySet($property)) {
                    continue;
                }

                if (in_array($property, array("title", "street", "iban"))) {
                    $query .= "," . $property . "=" . Connection::GetSQLEncryption($this->GetPropertyForSQL($property));
                } elseif (in_array($property, array("sepa_service_date", "sepa_voucher_date"))) {
                    $query .= "," . $property . "=" . Connection::GetSQLDateTime($this->GetProperty($property));
                } else {
                    $query .= "," . $property . "=" . $this->GetPropertyForSQL($property);
                }
            }
            $query .= " WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id");
        } else {
            $sepaServiceDate = strlen($this->GetProperty("sepa_service_date")) > 0
                ? $this->GetProperty("sepa_service_date")
                : "01.01.2020";
            $sepaVoucherDate = strlen($this->GetProperty("sepa_voucher_date")) > 0
                ? $this->GetProperty("sepa_voucher_date")
                : "01.01.2020";

            $query = "INSERT INTO company_unit (parent_unit_id, company_id, created, title, phone, email, zip_code, country, city, street, house, client_id, vat_payer_id, comment,
						bank_details, iban, bic, register, tax_number, payment_type, invoice_date, financial_statement_date, payroll_month, datev_format, datev_encoding, acc_meal_value_tax_flat,
						acc_food_subsidy_tax_free, acc_gross_salary, acc_grant_of_materials, acc_internet_subsidy_tax, acc_mobile_subsidy_tax_free, acc_recreation_subsidy_tax_flat, acc_net_income,
						payment_method, tax_consultant, app_logo_image, app_logo_mini_image, agreement_enable, voucher_logo_image, acc_bonus_tax_flat, acc_transport_tax_free, acc_child_care_tax_free, acc_travel_tax_free,
						acc_daily_allowance, acc_gift, acc_corporate_health_management,
						acc_ticket, acc_accommodation, acc_hospitality, acc_parking, acc_other, acc_travel_costs, acc_creditor,
						sepa_service, sepa_voucher, sepa_service_date, sepa_voucher_date, reg_email_text)
						VALUES (
						" . $this->GetPropertyForSQL("parent_unit_id") . ",
						" . $this->GetPropertyForSQL("company_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . Connection::GetSQLEncryption($this->GetPropertyForSQL("title")) . ",
						" . $this->GetPropertyForSQL("phone") . ",
						" . $this->GetPropertyForSQL("email") . ",
						" . $this->GetPropertyForSQL("zip_code") . ",
						" . $this->GetPropertyForSQL("country") . ",
						" . $this->GetPropertyForSQL("city") . ",
						" . Connection::GetSQLEncryption($this->GetPropertyForSQL("street")) . ",
						" . $this->GetPropertyForSQL("house") . ",
						" . $this->GetPropertyForSQL("client_id") . ",
						" . $this->GetPropertyForSQL("vat_payer_id") . ",
						" . $this->GetPropertyForSQL("comment") . ",
						" . $this->GetPropertyForSQL("bank_details") . ",
						" . Connection::GetSQLEncryption($this->GetPropertyForSQL("iban")) . ",
						" . $this->GetPropertyForSQL("bic") . ",
						" . $this->GetPropertyForSQL("register") . ",
						" . $this->GetPropertyForSQL("tax_number") . ",
						" . $this->GetPropertyForSQL("payment_type") . ",
						" . $this->GetPropertyForSQL("invoice_date") . ",
						" . $this->GetPropertyForSQL("financial_statement_date") . ",
						" . $this->GetPropertyForSQL("payroll_month") . ",
						" . $this->GetPropertyForSQL("datev_format") . ",
                        " . $this->GetPropertyForSQL("datev_encoding") . ",
						" . $this->GetPropertyForSQL("acc_meal_value_tax_flat") . ",
						" . $this->GetPropertyForSQL("acc_food_subsidy_tax_free") . ",
						" . $this->GetPropertyForSQL("acc_gross_salary") . ",
						" . $this->GetPropertyForSQL("acc_grant_of_materials") . ",
						" . $this->GetPropertyForSQL("acc_internet_subsidy_tax") . ",
						" . $this->GetPropertyForSQL("acc_mobile_subsidy_tax_free") . ",
						" . $this->GetPropertyForSQL("acc_recreation_subsidy_tax_flat") . ",
						" . $this->GetPropertyForSQL("acc_net_income") . ",
						" . $this->GetPropertyForSQL("payment_method") . ",
						" . $this->GetPropertyForSQL("tax_consultant") . ",
						" . $this->GetPropertyForSQL("app_logo_image") . ",
						" . $this->GetPropertyForSQL("app_logo_mini_image") . ",
						" . $this->GetPropertyForSQL("agreement_enable") . ",
                        " . $this->GetPropertyForSQL("voucher_logo_image") . ",
						" . $this->GetPropertyForSQL("acc_bonus_tax_flat") . ",
                        " . $this->GetPropertyForSQL("acc_transport_tax_free") . ",
                        " . $this->GetPropertyForSQL("acc_child_care_tax_free") . ",
                        " . $this->GetPropertyForSQL("acc_travel_tax_free") . ",
                        " . $this->GetPropertyForSQL("acc_daily_allowance") . ",
                        " . $this->GetPropertyForSQL("acc_gift") . ",
                        " . $this->GetPropertyForSQL("acc_corporate_health_management") . ",
                        " . $this->GetPropertyForSQL("acc_ticket") . ",
                        " . $this->GetPropertyForSQL("acc_accommodation") . ",
                        " . $this->GetPropertyForSQL("acc_hospitality") . ",
                        " . $this->GetPropertyForSQL("acc_parking") . ",
                        " . $this->GetPropertyForSQL("acc_other") . ",
                        " . $this->GetPropertyForSQL("acc_travel_costs") . ",
                        " . $this->GetPropertyForSQL("acc_creditor") . ",
                        " . $this->GetPropertyForSQL("sepa_service") . ",
                        " . $this->GetPropertyForSQL("sepa_voucher") . ",
                        " . Connection::GetSQLDateTime($sepaServiceDate) . ",
                        " . Connection::GetSQLDateTime($sepaVoucherDate) . ",
                        " . $this->GetPropertyForSQL("reg_email_text") . ")
					RETURNING company_unit_id";
            $this->SetProperty("archive", "N");
        }
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("company_unit_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("company_unit_id") > 0) {
            $this->SetProperty("company_unit_id", $stmt->GetLastInsertID());
            self::SetCustomerGUID($this->GetProperty("company_unit_id"));
            $this->SaveMonthlyPrice();
        }
        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }
        //update company_id field of child company units
        $childIDs = CompanyUnitList::AddChildIDs(array($this->GetProperty("company_unit_id")));
        if (count($childIDs) > 0) {
            $stmt->Execute("UPDATE company_unit
                        SET company_id=(SELECT company_id FROM company_unit WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id") . ")
                        WHERE company_unit_id IN(" . implode(", ", $childIDs) . ")");
        }

        //create or update company
        if (!$this->GetProperty("parent_unit_id")) {
            $company = new Company($this->module);
            $company->LoadFromObject($this);
            $company->Save();
            $this->SetProperty("company_id", $company->GetProperty("company_id"));
            $stmt->Execute("UPDATE company_unit SET company_id=" . $company->GetIntProperty("company_id") . " WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id"));
        }

        if (!$new) {
            if (
                $ibanBeforeSave != trim($this->GetProperty("iban")) ||
                $bicBeforeSave != trim($this->GetProperty("bic")) ||
                $bankNameBeforeSave != trim($this->GetProperty("bank_details")) ||
                $sepaServiceBeforeSave != trim($this->GetProperty("sepa_service")) ||
                $sepaVoucherBeforeSave != trim($this->GetProperty("sepa_voucher")) ||
                $sepaServiceDateBeforeSave != date("Y-m-d", strtotime($this->GetProperty("sepa_service_date"))) ||
                $sepaVoucherDateBeforeSave != date("Y-m-d", strtotime($this->GetProperty("sepa_voucher_date")))
            ) {
                //clear columns for update master data export
                $query = "UPDATE company_unit SET
                    master_data_service_update_id=NULL,
                    master_data_voucher_update_id=NULL,
                    master_data_sepa_service_update_id=NULL,
                    master_data_sepa_voucher_update_id=NULL
                    WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id");

                $stmt->Execute($query);
            }


            $contract = new Contract("product");
            foreach ($voucherProductExistBefore as $code => $existBefore) {
                if (
                    !$existBefore && $contract->LoadLatestActiveContract(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $this->GetProperty("company_unit_id"),
                        Product::GetProductIDByCode($code),
                        false
                    )
                ) {
                    //clear column for new master data export
                    $query = "UPDATE company_unit SET
                    master_data_voucher_id=NULL,
                    master_data_sepa_voucher_id=NULL
                    WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id");

                    $stmt->Execute($query);
                    break;
                }
            }
        }
        $this->PrepareBeforeShow();

        return true;
    }


    private function SaveOptions(array $products)
    {
        $moduleProduct = "product";
        $saved = 0;

        $billingUser = new User();
        $billingUser->LoadByID(BILLING_USER_ID);

        $user = new User();
        $user->LoadBySession();
        if (!$user->Validate(array("root"))) {
            $voucherProductList = ProductList::GetVoucherProductList();

            $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($this->GetProperty("company_unit_id"));
            $employeeListWithReceipt = ReceiptList::GetApprovedReceiptEmployeeIDs($employeeList);
            $contract = new Contract($this->module);
            $productList = $contract->GetCompanyUnitActiveProductList(
                $this->GetProperty("company_unit_id"),
                array_column($voucherProductList, "product_id"),
                $employeeList,
                $employeeListWithReceipt
            );

            foreach ($products as $id => $product) {
                if ($product["start_date"] != null) {
                    //add voucher contract only if there's other voucher service enabled
                    if (in_array($id, array_column($voucherProductList, "product_id"))) {
                        if ($productList == null) {
                            $this->AddError("voucher-service-start-date-error", $this->module, array(
                                "service_title" => GetTranslation(
                                    "product-" . Product::GetProductCodeByID($id),
                                    "product"
                                )
                            ));
                            $this->AddErrorField("Product[" . $id . "][start_date]", "product");

                            return false;
                        }
                    }
                    $currentMonthFirstDayTime = strtotime(date("Y-m-01"));
                    $startDateTime = strtotime($product['start_date']);
                    $currentDateTime = strtotime(GetCurrentDate());

                    if (
                        $startDateTime == $currentMonthFirstDayTime ||
                        ($startDateTime > $currentDateTime && date("d", $startDateTime) == '01')
                    ) {
                        continue;
                    }

                    $this->AddError(
                        "voucher-start-date-not-1st",
                        $this->module,
                        array("product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product"))
                    );
                    $this->AddErrorField("Product[" . $id . "][start_date]", "product");

                    return false;
                }

                if ($product["end_date"] != null) {
                    $endDateMonthEnd = strtotime(date("Y-m-t", strtotime($product['end_date'])));
                    $currentMonthEnd = strtotime(date("Y-m-t"));
                    $endDateTime = strtotime($product['end_date']);

                    if ($endDateTime == $endDateMonthEnd && $endDateTime >= $currentMonthEnd) {
                        continue;
                    }

                    $this->AddError(
                        "contract-end-date-not-future",
                        $this->module,
                        array("product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product"))
                    );
                    $this->AddErrorField("Product[" . $id . "][end_date]", "product");

                    return false;
                }

                if (!empty($product["date_of_params"])) {
                    $dateOfParams = strtotime($product["date_of_params"]);
                    $firstDayCurrentMonth = strtotime(date("Y-m-1"));
                    $currentDay = strtotime(date("Y-m-d"));

                    if (
                        $dateOfParams < $firstDayCurrentMonth ||
                        (date("d", $dateOfParams) != 1 && $currentDay != $dateOfParams)
                    ) {
                        $this->AddError(
                            "date-of-params-is-wrong",
                            $this->module,
                            ["product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                        );
                        $this->AddErrorField("Product[" . $id . "][date_of_params]", "product");

                        return false;
                    }
                }
            }
        }

        $baseModuleID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
        $baseModuleKey = $baseModuleID;
        //move base module to end of array "products" for correct update end_date and start_date
        if (count($products) > 0) {
            $productsWithoutBaseModule = $products;
            unset($productsWithoutBaseModule[$baseModuleKey]);
            $productsWithoutBaseModule[$baseModuleKey] = $products[$baseModuleKey];
            $products = $productsWithoutBaseModule;
        }

        foreach ($products as $id => $product) {
            $optionList = new OptionList($moduleProduct);
            $optionList->LoadOptionListForAdmin($id, OPTION_LEVEL_COMPANY_UNIT);

            $created = null;
            $duplicate = false;
            if (isset($product['date_of_params']) && $product['date_of_params']) {
                $created = $product['date_of_params'];
            } elseif (isset($product["start_date"]) && $product["start_date"] && (strtotime($product["start_date"]) < time())) {
                $created = $product['start_date'];
                $duplicate = true;
            }
            if (Product::GetProductCodeByID($id) != PRODUCT__BASE__MAIN) {
                $baseContract = new Contract($moduleProduct);
                $result = $baseContract->LoadLatestActiveContract(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->GetProperty("company_unit_id"),
                    Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
                );

                $baseContract = $result == false ? $products[$baseModuleKey] : $baseContract->GetProperties();

                if (!$baseContract["start_date"] && isset($product['start_date']) && $product['start_date']) {
                    $this->AddError(
                        "error-company-unit-base-product-notset",
                        "product",
                        ['base_product' => GetTranslation("product-" . PRODUCT__BASE__MAIN, "product")]
                    );
                    $this->AddErrorField("Product[" . Product::GetProductIDByCode(PRODUCT__BASE__MAIN) . "][start_date]");
                    break;
                }

                if (isset($product['start_date']) && $product['start_date'] && (strtotime($baseContract["start_date"]) > strtotime($product['start_date']))) {
                    $this->AddError(
                        "error-company-unit-base-product-start_date",
                        "product",
                        [
                            'product' => GetTranslation("product-" . Product::GetProductCodeByID($id), "product"),
                            'base_product' => GetTranslation("product-" . PRODUCT__BASE__MAIN, "product"),
                            'start_date' => FormatDate("d.m.Y", $baseContract["start_date"]),
                        ]
                    );
                    $this->AddErrorField("Product[" . $id . "][start_date]");
                    continue;
                }
            }

            if (Product::GetProductCodeByID($id) == PRODUCT__FOOD__MAIN) {
                $foodFlexOption = Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_OPTION);
                if (isset($product["Option"][$foodFlexOption])) {
                    $unitsForTransfer = Option::GetOptionIDByCode(OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER);
                    $currentFlex = Option::GetOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $foodFlexOption,
                        $this->GetProperty("company_unit_id"),
                        $created
                    );
                    if ($currentFlex != $product["Option"][$foodFlexOption]) {
                        $currentTransfer = Option::GetOptionValue(
                            OPTION_LEVEL_COMPANY_UNIT,
                            $unitsForTransfer,
                            $this->GetProperty("company_unit_id"),
                            $created
                        );
                        if ($product["Option"][$foodFlexOption] == "Y") {
                            $product["Option"][$unitsForTransfer] = 0;
                        } elseif ($currentTransfer == $product["Option"][$foodFlexOption]) {
                            $product["Option"][$unitsForTransfer] = null;
                        }
                    }
                }
            }

            if (Product::GetProductCodeByID($id) == PRODUCT__AD__MAIN) {
                $adContract = new Contract($moduleProduct);
                $activeContract = $adContract->LoadLatestActiveContract(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $this->GetProperty("company_unit_id"),
                    $id,
                    true
                );
                if (
                    $activeContract
                    || !empty($product['start_date'])
                    && (empty($product['end_date'])
                        || strtotime($product['end_date']) >= GetCurrentDate())
                ) {
                    $adReceiptOption = Option::GetOptionIDByCode(OPTION__AD__MAIN__RECEIPT_OPTION);
                    if (isset($product["Option"][$adReceiptOption])) {
                        $yearlyPaymentMonth = Option::GetOptionIDByCode(OPTION__AD__MAIN__PAYMENT_MONTH);
                        $yearlyPaymentMonthValue = $product["Option"][$yearlyPaymentMonth] != null
                            ? $product["Option"][$yearlyPaymentMonth]
                            : Option::GetOptionValue(
                                OPTION_LEVEL_GLOBAL,
                                OPTION__AD__MAIN__PAYMENT_MONTH,
                                0,
                                $created ?? GetCurrentDate()
                            );
                        $currentReceiptOption = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_COMPANY_UNIT,
                            OPTION__AD__MAIN__RECEIPT_OPTION,
                            $this->GetProperty("company_unit_id"),
                            $created ?? GetCurrentDate()
                        );
                        if (
                            ($product["Option"][$adReceiptOption] == "yearly"
                                || $product["Option"][$adReceiptOption] == null && $currentReceiptOption == "yearly")
                            && $yearlyPaymentMonthValue == null
                        ) {
                            $this->AddError(
                                "error-ad-yearly-payment-month-empty",
                                "product"
                            );
                            $this->AddErrorField("Product[" . $id . "][Option][" . $yearlyPaymentMonth . "]");
                            continue;
                        }
                    }
                }
            }

            foreach ($optionList->GetItems() as $item) {
                $optionValue = '';
                if (isset($product["Option"][$item["option_id"]])) {
                    $optionValue = $product["Option"][$item["option_id"]];
                }

                $option = new Option($moduleProduct);

                if ($duplicate) {
                    $result = $option->SaveOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $item["option_id"],
                        $optionValue ?? null,
                        $this->GetProperty("company_unit_id"),
                        $created,
                        $billingUser
                    );
                } else {
                    $result = $option->SaveOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $item["option_id"],
                        $optionValue ?? null,
                        $this->GetProperty("company_unit_id"),
                        $created,
                        $user
                    );
                }

                if (!$result) {
                    $this->AppendErrorsFromObject($option);
                    $this->AppendErrorFieldsFromObject($option);
                }
                $this->AppendMessagesFromObject($option);
            }

            $saved++;
            $contract = new Contract($moduleProduct);
            $result = $contract->OnOptionUpdate(
                OPTION_LEVEL_COMPANY_UNIT,
                $id,
                $this->GetProperty("company_unit_id"),
                $product["contract_id"] ?? null,
                $product["start_date"] ?? null,
                $product["end_date"] ?? null
            );

            $voucherProductList = ProductList::GetVoucherProductList(true);
            $productCodeList = array_column($voucherProductList, "code");

            if (!$result) {
                $this->AppendErrorsFromObject($contract);
                $this->AppendErrorFieldsFromObject($contract);
                break;
            }

            if (
                in_array(
                    Product::GetProductCodeByID($id),
                    $productCodeList
                ) && $this->GetProperty("creditor_number") == null
            ) {
                $this->SetProperty(
                    "creditor_number",
                    CompanyUnit::GetPropertyValue("creditor_number", $this->GetProperty("company_unit_id"))
                );
            }

            if ($product["end_date"] == null) {
                continue;
            }

            $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($this->GetProperty("company_unit_id"));
            foreach ($employeeList as $employeeID) {
                $contract = new Contract($moduleProduct);
                $contract->LoadLatestActiveContract(OPTION_LEVEL_EMPLOYEE, $employeeID, $id);
                if (!$contract->IsPropertySet("contract_id")) {
                    continue;
                }

                $result = $contract->OnOptionUpdate(
                    OPTION_LEVEL_EMPLOYEE,
                    $id,
                    $employeeID,
                    $contract->GetProperty("contract_id"),
                    null,
                    $product["end_date"]
                );
                if (!$result) {
                    $this->AppendErrorsFromObject($contract);
                    $this->AppendErrorFieldsFromObject($contract);
                    break;
                }
            }
        }
        if ($saved > 0) {
            $link = $this->GetProperty('Link');
            Operation::Save($link, "company", "company_save_option", $this->GetProperty("company_unit_id"));
        }

        return true;
    }

    /**
     * Action on company unit's contract end.
     *
     * @param String $endDate - date of company unit's contract end
     */
    private function End($endDate)
    {
        $moduleProduct = "product";
        $productList = $this->GetProperty("Product");
        foreach ($productList as $key => $value) {
            $contract = new Contract($moduleProduct);
            $contract->LoadLatestActiveContract(OPTION_LEVEL_COMPANY_UNIT, $this->GetProperty("company_unit_id"), $key);
            $contracts[] = $contract;
            if ($contract->GetProperty("end_date")) {
                $productList[$key]['end_date'] = min($contract->GetProperty("end_date"), $endDate);
            } elseif ($contract->GetProperty("start_date") && strtotime($contract->GetProperty("start_date")) < strtotime($endDate)) {
                $productEndDay = $productList[$key]['end_date'] ?? $endDate;
                $productList[$key]['end_date'] = min($endDate, $productEndDay ?: $endDate);
            } elseif (($productList[$key]["start_date"] ?? false) && strtotime($productList[$key]["start_date"]) < strtotime($endDate)) {
                $productList[$key]['end_date'] = $endDate;
            } else {
                $productList[$key]['start_date'] = "";
            }
        }
        $this->SetProperty("Product", $productList);

        $companyUnitList = new CompanyUnitList($this->module);
        $companyUnitList->Remove(array($this->GetProperty("company_unit_id")), "admin", $endDate);

        $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($this->GetProperty("company_unit_id"));
        $employee = new Employee($this->module);
        foreach ($employeeList as $employeeID) {
            $employee->EndByCron($employeeID, $endDate, true);
        }
    }

    /**
     * Finishes all the contracts of company unit and deactivates it when end_date of base module contract was passed
     *
     * @param int $companyUnitID
     * @param string $endDate
     * @param bool $endBase do we need to end base module?
     * @param bool $removeList for cases when $companyUnitList->Remove isn't needed
     */
    public function EndByCron($companyUnitID, $endDate, $endBase = false, $removeList = true)
    {
        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
            $productList = new ProductList("product");
            $productList->LoadProductListForAdmin($productGroupList->_items[$i]["group_id"]);
            for ($j = 0; $j < $productList->GetCountItems(); $j++) {
                $contract = new Contract("product");
                $contract->LoadLatestActiveContract(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $companyUnitID,
                    $productList->_items[$j]["product_id"]
                );

                if (($productList->_items[$j]["code"] == PRODUCT__BASE__MAIN && !$endBase) || !$contract->GetProperty("contract_id")) {
                    continue;
                }

                $contract->OnOptionUpdate(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $productList->_items[$j]["product_id"],
                    $companyUnitID,
                    $contract->GetProperty("contract_id"),
                    null,
                    $endDate
                );
            }
        }

        if ($removeList) {
            $companyUnitList = new CompanyUnitList($this->module);
            $companyUnitList->Remove(array($companyUnitID), "admin", $endDate);
        }

        //if we are disabling base module, we should disable employees as well
        if (!$endBase) {
            return;
        }

        $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        $employee = new Employee($this->module);
        foreach ($employeeList as $employeeID) {
            $employee->EndByCron($employeeID, $endDate, true);
        }
    }


    /**
     * Validates input data when trying to create/update company_unit from admin panel. Also turns incorrect int/float properties into null.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        if (!$this->IsPropertySet("agreement_enable")) {
            $this->SetProperty("agreement_enable", "N");
        }

        if (!($this->GetIntProperty("parent_unit_id") > 0)) {
            $this->RemoveProperty("parent_unit_id");
        }

        if (!($this->GetIntProperty("company_id") > 0)) {
            $this->RemoveProperty("company_id");
        }

        if (!$this->ValidateNotEmpty("title")) {
            $this->AddError("company-unit-title-empty", $this->module);
            $this->AddErrorField("title");
        }

        if (!$this->GetProperty("parent_unit_id")) {
            $stmt = GetStatement();
            $query = "SELECT COUNT(*) FROM company_unit WHERE company_id=" . $this->GetIntProperty("company_id") . " AND parent_unit_id IS NULL AND company_unit_id!=" . $this->GetIntProperty("company_unit_id");
            if ($stmt->FetchField($query) > 0) {
                $this->AddError("company-unit-parent-empty", $this->module);
                $this->AddErrorField("parent_unit_id");
            }
        }

        if ($this->GetIntProperty("invoice_date") < 1 || $this->GetIntProperty("invoice_date") > 31) {
            $this->SetProperty("invoice_date", "1");
        }

        if ($this->GetIntProperty("financial_statement_date") < 1 || $this->GetIntProperty("financial_statement_date") > 31) {
            $this->SetProperty("financial_statement_date", "15");
        }

        //save only int values
        $this->SetProperty("invoice_date", $this->GetIntProperty("invoice_date"));
        $this->SetProperty("financial_statement_date", $this->GetIntProperty("financial_statement_date"));

        if (!$this->IsPropertySet("payroll_month")) {
            $this->SetProperty("payroll_month", "last_month");
        }

        if (!$this->GetProperty("payment_type")) {
            $this->SetProperty("payment_type", "monthly");
        }

        if (!$this->GetProperty("payment_method")) {
            $this->SetProperty("payment_method", "sepa");
        }

        if (preg_match("/lug/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "lug");
        } elseif (preg_match("/lodas/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "lodas");
        } elseif (preg_match("/logga/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "logga");
        } elseif (preg_match("/topas/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "topas");
        } elseif (preg_match("/addison/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "addison");
        } elseif (preg_match("/lexware/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "lexware");
        } elseif (preg_match("/perforce/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "perforce");
        } elseif (preg_match("/sage/i", $this->GetProperty("datev_format"))) {
            $this->SetProperty("datev_format", "sage");
        } else {
            $this->SetProperty("datev_format", "lodas");
        }

        if (!$this->IsPropertySet("datev_encoding")) {
            $this->SetProperty("datev_encoding", "utf-8");
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        return !$this->HasErrors();
    }

    /**
     * Validates user company unit Role
     *
     * @param int $companyUnitID company_unit_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($companyUnitID, $userID = null)
    {
        if (!$companyUnitID) {
            return true;
        }

        $permissionName = "company_unit";

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        if ($user->Validate(array($permissionName))) {
            return true;
        } else {
            $companyUnitIDs = $user->GetPermissionLinkIDs($permissionName);
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) == 0) {
                return false;
            }
        }

        return in_array($companyUnitID, $companyUnitIDs) ? true : false;
    }

    /**
     * Returns list of replacements
     *
     * @return array
     */
    public function GetReplacementsList()
    {
        $properties = array(
            "title",
            "street",
            "house",
            "zip_code",
            "city"
        );

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            if ($property == "title") {
                $replacements[] = array(
                    "template" => "%company_unit_" . $property . "%",
                    "translation" => GetTranslation("replacement-company-" . $property, $this->module)
                );
                $values["company_unit_" . $property] = $this->GetProperty($property);
            } else {
                $replacements[] = array(
                    "template" => "%company_" . $property . "%",
                    "translation" => GetTranslation("replacement-company-" . $property, $this->module)
                );
                $values["company_" . $property] = $this->GetProperty($property);
            }
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    /**
     * Save to db default monthly service price
     */
    private function SaveMonthlyPrice()
    {
        $moduleProduct = "product";
        $option = new Option($moduleProduct);

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT code, value FROM config WHERE group_code='o_option'";
        $optionDefaultValues = $stmt->FetchList($query);
        if ($optionDefaultValues === false) {
            $this->AddError("sql-error");

            return;
        }

        foreach ($optionDefaultValues as $defaultValue) {
            $option->SaveOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                Option::GetOptionIDByCode($defaultValue['code']),
                $defaultValue['value'],
                $this->GetProperty("company_unit_id")
            );
        }
    }

    /**
     * Generates and saves to db customer_guid
     *
     * @param int $companyUnitID company_unit_id of company unit guid to be generated for
     */
    public static function SetCustomerGUID($companyUnitID)
    {
        $stmt = GetStatement();
        $query = "SELECT created FROM company_unit WHERE company_unit_id=" . intval($companyUnitID);
        $created = $stmt->FetchField($query);

        $guidPrefix = date("Y", strtotime($created));
        $guidSuffix = 100000;

        $query = "SELECT MAX(RIGHT(customer_guid, 6)::int) FROM company_unit WHERE customer_guid IS NOT NULL";
        $max = $stmt->FetchField($query);
        if ($max != null) {
            $guidSuffix = $max;
        }

        $customerGUID = $guidPrefix . ($guidSuffix + 1);

        $query = "UPDATE company_unit SET customer_guid=" . Connection::GetSQLString($customerGUID) . "
					WHERE company_unit_id=" . intval($companyUnitID) . " AND customer_guid IS NULL";
        $stmt->Execute($query);
    }

    /**
     * Returns value of selected property
     *
     * @param string $property property of option which value to be returned
     * @param int $companyUnitID company_unit_id whose value to be returned
     *
     * @return string $value of property
     */
    public static function GetPropertyValue($property, $companyUnitID, $date = null)
    {
        if ($date == null) {
            $stmt = GetStatement();

            $query = in_array($property, array("title", "street"))
                ? "SELECT " . Connection::GetSQLDecryption($property) . " FROM company_unit WHERE company_unit_id=" . $companyUnitID
                : "SELECT " . $property . " FROM company_unit WHERE company_unit_id=" . $companyUnitID;
            $value = $stmt->FetchField($query);
        } else {
            $stmt = GetStatement(DB_CONTROL);
            $where = [];
            $where[] = "property_name = " . Connection::GetSQLString($property);
            $where[] = "company_unit_id = " . Connection::GetSQLString($companyUnitID);
            $query = "SELECT *
						FROM company_unit_history
						WHERE " . implode(" AND ", $where) . "
						ORDER BY created ASC, value_id ASC";
            $valueList = $stmt->FetchList($query);
            foreach ($valueList as $value) {
                if (strtotime($value["created"]) > strtotime($date)) {
                    continue;
                }

                $value = $value["value"];
            }
        }

        return $value;
    }

    /**
     * Returns value of selected date.
     * If value is not set for selected company_unit exactly then function will try to find the value for higher-level company_units
     *
     * @param int $companyUnitID company_unit_id whose value to be returned
     * @param string $property property of option which value to be returned
     *
     * @return string|null string if value is found or null otherwise
     */
    public static function GetInheritablePropertyCompanyUnit($companyUnitID, $property)
    {
        $searchList = self::GetInheritableCompanyUnitSearchList($companyUnitID);
        foreach ($searchList as $search) {
            $value = self::GetPropertyValue($property, $search["entity_id"]);
            if ($value !== null && $value !== false) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Returns array of entityID where inheritable properties should be searched
     *
     * @param int $companyUnitID company_unit_id whose value is searched
     *
     * @return array
     */

    private static function GetInheritableCompanyUnitSearchList($companyUnitID)
    {
        $searchList = array();

        $path2root = CompanyUnitList::GetCompanyUnitPath2Root($companyUnitID, true);
        foreach ($path2root as $companyUnitID) {
            $searchList[] = array("entity_id" => $companyUnitID);
        }

        return $searchList;
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $companyUnitID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListCompanyUnit($property, $companyUnitID, $languageCode = null)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name, created_from
					FROM company_unit_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND company_unit_id=" . intval($companyUnitID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        for ($i = 0; $i < count($valueList); $i++) {
            if ($valueList[$i]["property_name"] == "parent_unit_id") {
                $valueList[$i]["value"] = $stmt->FetchField("SELECT title FROM company_unit WHERE company_unit_id=" . intval($valueList[$i]["value"]));
            }

            $valueList[$i]["user_name"] = User::GetNameByID($valueList[$i]["user_id"]);

            if ($valueList[$i]["property_name"] != "datev_encoding") {
                continue;
            }

            if ($valueList[$i]["value"] == "utf-8") {
                $valueList[$i]["value"] = GetTranslation("utf8", "company", array(), $languageCode);
            }
            if ($valueList[$i]["value"] != "windows-1252") {
                continue;
            }

            $valueList[$i]["value"] = GetTranslation("ansi", "company", array(), $languageCode);
        }

        return $valueList;
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */

    public function SaveHistory($currentPropertyList)
    {
        $stmt = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();

        $propertyList = array(
            "title",
            "street",
            "house",
            "zip_code",
            "city",
            "country",
            "phone",
            "email",
            "tax_consultant",
            "client_id",
            "vat_payer_id",
            "comment",
            "payment_type",
            "invoice_date",
            "financial_statement_date",
            "payroll_month",
            "datev_format",
            "datev_encoding",
            "acc_meal_value_tax_flat",
            "acc_food_subsidy_tax_free",
            "acc_gross_salary",
            "acc_grant_of_materials",
            "acc_internet_subsidy_tax",
            "acc_mobile_subsidy_tax_free",
            "acc_recreation_subsidy_tax_flat",
            "acc_net_income",
            "acc_bonus_tax_flat",
            "acc_transport_tax_free",
            "archive",
            "acc_daily_allowance",
            "acc_gift",
            "acc_corporate_health_management",
            "acc_ticket",
            "acc_accommodation",
            "acc_hospitality",
            "acc_parking",
            "acc_other",
            "acc_travel_costs",
            "acc_creditor",
            "sepa_service",
            "sepa_voucher",
            "sepa_service_date",
            "sepa_voucher_date",
            "reg_email_text",
        );
        if ($this->GetProperty("parent_unit_id")) {
            array_push($propertyList, "parent_unit_id");
        } else {
            array_push($propertyList, "colorscheme", "bank_details", "iban", "bic", "register", "tax_number");
        }

        foreach ($propertyList as $key) {
            if (!$this->IsPropertySet($key) || $currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            $query = "INSERT INTO company_unit_history (company_unit_id, property_name, value, created, user_id, created_from)
            VALUES (
            " . $this->GetIntProperty("company_unit_id") . ",
            " . Connection::GetSQLString($key) . ",
            " . Connection::GetSQLString($this->GetProperty($key)) . ",
            " . Connection::GetSQLString(GetCurrentDateTime()) . ",
            " . $user->GetIntProperty("user_id") . ",
            " . Connection::GetSQLString($this->GetProperty("created_from")) . ")
            RETURNING value_id";
            if (!$stmt->Execute($query)) {
                return false;
            }
        }

        if (!$this->GetIntProperty("value_id") > 0) {
            $this->SetProperty("value_id", $stmt->GetLastInsertID());
        }

        return true;
    }

    /**
     * Returns array of current value of properties
     *
     * @param int $id company_unit_id whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement();
        $query = "SELECT u.parent_unit_id,
						u.phone, u.email, u.zip_code, u.country, u.city, u.house,
						u.client_id, u.vat_payer_id, u.comment, u.bank_details, u.bic, u.register, u.tax_consultant,
						u.tax_number, u.payment_type, u.invoice_date, u.financial_statement_date, u.payroll_month, u.datev_format, u.datev_encoding,
						u.acc_meal_value_tax_flat, u.acc_food_subsidy_tax_free, u.acc_gross_salary, u.acc_grant_of_materials,
						u.acc_internet_subsidy_tax, u.acc_mobile_subsidy_tax_free, u.acc_recreation_subsidy_tax_flat, u.acc_net_income,
						c.colorscheme, u.acc_bonus_tax_flat, u.acc_transport_tax_free, u.acc_child_care_tax_free, u.acc_travel_tax_free,
						u.acc_daily_allowance, u.acc_gift, u.acc_corporate_health_management,
						u.acc_ticket, u.acc_accommodation, u.acc_hospitality,
						u.acc_parking, u.acc_other, u.acc_travel_costs, u.acc_creditor,
						u.sepa_service, u.sepa_voucher, u.sepa_service_date, u.sepa_voucher_date, u.reg_email_text,
						" . Connection::GetSQLDecryption("u.title") . " AS title,
						" . Connection::GetSQLDecryption("u.street") . " AS street,
						" . Connection::GetSQLDecryption("u.iban") . " AS iban
					FROM company_unit AS u
						LEFT JOIN company AS c ON c.company_id=u.company_id
					WHERE u.company_unit_id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Return title of company unit by id.
     *
     * @param int $id of required company unit
     *
     * @return string
     */
    static function GetTitleByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT " . Connection::GetSQLDecryption("title") . " AS title
        FROM company_unit
            WHERE company_unit_id=" . intval($id);

        return $stmt->FetchField($query);
    }

    /**
     * Return id by title.
     * @param int $title of required company unit
     * @return string
     */
    static function GetIDByTitle($title)
    {
        $stmt = GetStatement();
        $query = "SELECT company_unit_id
         FROM company_unit
            WHERE " . Connection::GetSQLDecryption("title") . "
            =" . Connection::GetSQLString($title);

        return $stmt->FetchField($query);
    }

    /**
     * Returns array of image resize settings for $key image necessary for admin image edit component initializing
     *
     * @param string $key image key
     *
     * @return mixed[][]
     */
    public function GetImageParams($key)
    {
        $paramList = array();
        for ($i = 0; $i < count($this->params[$key]); $i++) {
            $paramList[] = array(
                "Name" => $this->params[$key][$i]['Name'],
                "SourceName" => $this->params[$key][$i]['SourceName'],
                "Width" => $this->params[$key][$i]['Width'],
                "Height" => $this->params[$key][$i]['Height'],
                "Resize" => $this->params[$key][$i]['Resize'],
                "X1" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "X1"),
                "Y1" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "Y1"),
                "X2" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "X2"),
                "Y2" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "Y2")
            );
        }

        return $paramList;
    }

    private function PrepareBeforeShow()
    {
        $this->PrepareImages();

        $businessTerms = array();
        $businessTermCodes = array(
            "business_terms_1",
            "business_terms_2",
            "business_terms_3",
            "business_terms_4",
            "business_terms_5",
            "business_terms_6"
        );
        $config = new Config();
        foreach ($businessTermCodes as $code) {
            $config->LoadByID(Config::GetIDByCode($code));
            if (!$config->GetProperty("value")) {
                continue;
            }

            $businessTerms[] = array(
                "code" => $code,
                "translation" => GetTranslation("config-" . $code),
                "value" => $config->GetProperty("value"),
                "value_download_url" => $config->GetProperty("value_download_url")
            );
        }
        $this->SetProperty("BusinessTerms", $businessTerms);
    }

    public function PrepareImages(): void
    {
        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__COMPANY, 'apps/');
            PrepareDownloadPath($this->_properties, $key . "_image", COMPANY_APP_LOGO_IMAGE_DIR, CONTAINER__COMPANY);
        }
    }


    /**
     * Tries to upload new $type image and initialize its config.
     * Resets current object $type image property by previously uploaded file if new file is not uploaded.
     *
     * @param string $savedImage previously uploaded filename
     * @param string $type image key
     *
     * @return bool false if error is occured during new image uploading or true if its uploaded successfully or no new image provided
     */
    private function SaveAppCompanyLogoImage($savedImage = "", $type = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__COMPANY);

        $newChannelImage = $fileStorage->Upload(
            $type . "_image",
            COMPANY_APP_LOGO_IMAGE_DIR,
            false,
            $this->_acceptMimeTypes
        );

        if ($newChannelImage) {
            $this->SetProperty($type . "_image", $newChannelImage["FileName"]);

            // Remove old image if it has different name
            if ($savedImage && $savedImage != $newChannelImage["FileName"]) {
                $fileStorage->Remove(COMPANY_APP_LOGO_IMAGE_DIR . $savedImage);
            }
        } else {
            if ($savedImage) {
                $this->SetProperty($type . "_image", $savedImage);
            } else {
                $this->SetProperty($type . "_image", null);
            }
        }

        $this->_properties[$type . "_image_config"]["Width"] = 0;
        $this->_properties[$type . "_image_config"]["Height"] = 0;

        if ($this->GetProperty($type . '_image')) {
            if ($info = @getimagesize(COMPANY_APP_LOGO_IMAGE_DIR . $this->GetProperty($type . '_image'))) {
                $this->_properties[$type . "_image_config"]["Width"] = $info[0];
                $this->_properties[$type . "_image_config"]["Height"] = $info[1];
            }
        }

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }

    /**
     * Loads info about activations and inactivations of this company_unit from company_unit_history
     *
     * @return array as result or false on fail.
     */
    public function LoadArchiveInfo()
    {
        if (!$this->GetProperty("company_unit_id")) {
            return false;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM company_unit_history
                    WHERE company_unit_id=" . $this->GetProperty("company_unit_id") . " AND property_name='archive'
                    ORDER BY created DESC";
        if (!$result = $stmt->FetchList($query)) {
            return false;
        }

        $userList = array_column($result, "user_id");
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " AS username, user_id FROM user_info WHERE user_id IN (" . implode(
            ",",
            $userList
        ) . ")";
        $userList = $stmt->FetchIndexedList($query, "user_id");
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['username'] = $userList[$result[$i]['user_id']]['username'];
        }

        return $result;
    }

    /**
     * Really totally remove Company unit and all related data from DB
     */
    public function RemoveCompanyUnitData()
    {

        $companyUnitIDs = array($this->GetIntProperty("company_unit_id"));
        $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);

        $employeeIDs = array();
        foreach ($companyUnitIDs as $companyUnitID) {
            $employeeIDs = array_merge($employeeIDs, EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID));
        }

        $userIDs = array();
        $receiptIDs = array();
        if ($employeeIDs) {
            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT user_id FROM employee WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $userIDs = array_keys($stmt->FetchIndexedList($query));

            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT receipt_id FROM receipt WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $receiptIDs = array_keys($stmt->FetchIndexedList($query));
        }

        $voucherIDs = array();
        if ($employeeIDs) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT voucher_id FROM voucher WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $voucherIDs = array_keys($stmt->FetchIndexedList($query));
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT invoice_id FROM invoice WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
        $invoiceIDs = $stmt->FetchIndexedList($query);
        $invoiceIDs = $invoiceIDs ? array_keys($invoiceIDs) : array();

        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("DELETE FROM company_unit_contract WHERE company_unit_id IN(" . implode(
            ", ",
            $companyUnitIDs
        ) . ")");
        $stmt->Execute("DELETE FROM company_unit_history WHERE company_unit_id IN(" . implode(
            ", ",
            $companyUnitIDs
        ) . ")");
        $stmt->Execute("DELETE FROM option_value_history WHERE level=" . Connection::GetSQLString(OPTION_LEVEL_COMPANY_UNIT) . " AND entity_id IN(" . implode(
            ", ",
            $companyUnitIDs
        ) . ")");

        if ($employeeIDs) {
            $stmt->Execute("DELETE FROM employee_contract WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")");
            $stmt->Execute("DELETE FROM employee_history WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")");
            $stmt->Execute("DELETE FROM option_value_history WHERE level=" . Connection::GetSQLString(OPTION_LEVEL_EMPLOYEE) . " AND entity_id IN(" . implode(
                ", ",
                $employeeIDs
            ) . ")");
        }

        //Remove partner Info
        $stmt->Execute("DELETE FROM partner_contract WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")");

        $stmt = GetStatement(DB_PERSONAL);

        $stmt->Execute("DELETE FROM contact WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")");

        if ($employeeIDs) {
            $stmt->Execute("DELETE FROM employee WHERE employee_id IN(" . implode(", ", $employeeIDs) . ")");
        }
        if ($userIDs) {
            $stmt->Execute("UPDATE device SET user_id=NULL WHERE user_id IN(" . implode(", ", $userIDs) . ")");
            $stmt->Execute("DELETE FROM user_permissions WHERE user_id IN(" . implode(", ", $userIDs) . ")");
            $stmt->Execute("DELETE FROM user_info WHERE user_id IN(" . implode(", ", $userIDs) . ")");
        }

        $stmt = GetStatement(DB_MAIN);

        $stmt->Execute("DELETE FROM company_unit WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")");
        $stmt->Execute("DELETE FROM company_unit_option_value WHERE company_unit_id IN(" . implode(
            ", ",
            $companyUnitIDs
        ) . ")");
        $stmt->Execute("DELETE FROM commission_line WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")");

        if ($employeeIDs) {
            $stmt->Execute("DELETE FROM employee_option_value WHERE employee_id IN(" . implode(
                ", ",
                $employeeIDs
            ) . ")");
        }

        if (count($receiptIDs) > 0) {
            $stmt->Execute("DELETE FROM receipt_line WHERE receipt_id IN(" . implode(", ", $receiptIDs) . ")");
            $stmt->Execute("DELETE FROM receipt_file WHERE receipt_id IN(" . implode(", ", $receiptIDs) . ")");
            $stmt->Execute("DELETE FROM receipt_comment WHERE receipt_id IN(" . implode(", ", $receiptIDs) . ")");
            $stmt->Execute("DELETE FROM receipt WHERE receipt_id IN(" . implode(", ", $receiptIDs) . ")");
        }

        if (count($voucherIDs) > 0) {
            $stmt->Execute("DELETE FROM voucher WHERE voucher_id IN(" . implode(", ", $voucherIDs) . ")");
        }

        if (count($invoiceIDs) > 0) {
            $stmt->Execute("DELETE FROM invoice_line WHERE invoice_id IN(" . implode(", ", $invoiceIDs) . ")");
            $stmt->Execute("DELETE FROM invoice WHERE invoice_id IN(" . implode(", ", $invoiceIDs) . ")");
        }

        $this->AddMessage(
            "company-unit-data-removed",
            "company",
            ['title' => $this->GetProperty("title"), 'id' => $this->GetIntProperty("company_unit_id")]
        );
    }

    public function SetAgreementEnabled($companyUnitId, $value)
    {
        $value = $value == 'Y' ? 'Y' : 'N';
        $stmt = GetStatement();
        $query = 'UPDATE company_unit SET agreement_enable=' . Connection::GetSQLString($value) . '
                WHERE company_unit_id=' . intval($companyUnitId);

        return $stmt->Execute($query);
    }

    /**
     * Generates and saves to DB creditor_number
     *
     * @param $companyUnitID
     */
    public static function SetCreditorNumber($companyUnitID)
    {
        $stmt = GetStatement(DB_MAIN);
        $guidSuffix = 70000000 - 1;

        $query = "SELECT MAX(RIGHT(creditor_number, 8)::int) FROM company_unit WHERE creditor_number IS NOT NULL";
        $max = $stmt->FetchField($query);
        if ($max != null && $max <= 79999998) {
            $guidSuffix = $max;
        }

        $creditorNumber = $guidSuffix + 1;

        $query = "UPDATE company_unit SET creditor_number=" . Connection::GetSQLString($creditorNumber) . "
					WHERE company_unit_id=" . intval($companyUnitID) . " AND creditor_number IS NULL";
        $stmt->Execute($query);
    }
}
