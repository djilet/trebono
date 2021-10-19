<?php

class Invoice extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of invoice properties to be loaded instantly
     */
    public function Invoice($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads invoice by its invoice_id
     *
     * @param int $id invoice_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT i.invoice_id, i.company_unit_id, i.invoice_guid, i.created, i.date_from, i.date_to, i.status, 
                        i.invoice_type, i.details_file, i.archive,
						" . Connection::GetSQLDecryption("c.title") . " AS company_unit_title 
					FROM invoice AS i 
						LEFT JOIN company_unit AS c ON c.company_unit_id=i.company_unit_id
					WHERE i.invoice_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("invoice_id")) {
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
        $this->SetProperty(
            "status_title",
            GetTranslation("invoice-status-" . $this->GetProperty("status"), $this->module)
        );

        $stmt = GetStatement();
        $query = "SELECT COALESCE(SUM(cost +
                               COALESCE(flex_unit_sum::numeric, 0) +
                               COALESCE(flex_percentage_sum::numeric, 0)
                        ), 0)
                FROM invoice_line WHERE invoice_id=" . $this->GetIntProperty("invoice_id");
        $cost = floatval($stmt->FetchField($query));
        if ($this->GetProperty("invoice_type") == "voucher_invoice") {
            $vatAmount = round($cost * Config::GetConfigValueByDate(
                "voucher_invoice_vat",
                $this->GetProperty("date_from")
            ) / 100, 2);
        } else {
            $vatAmount = round($cost * Config::GetConfigValueByDate(
                "invoice_vat",
                $this->GetProperty("date_from")
            ) / 100, 2);
        }

        $this->SetProperty("cost", $cost);
        $this->SetProperty("vat_amount", $vatAmount);
        $this->SetProperty("cost_with_vat", $cost + $vatAmount);
    }

    /**
     * Creates new invoice. Object must be loaded from request before the method will be called.
     * Required properties are: company_unit_id, date_from, date_to, is_cron (bool)
     *
     * @return bool true if invoice is created successfully or false on failure
     */
    public function Create()
    {
        if (!$this->IsPropertySet("invoice_type")) {
            $this->SetProperty("invoice_type", "invoice");
        }
        $stmt = GetStatement();
        $query = "INSERT INTO invoice (company_unit_id, created, date_from, date_to, status, invoice_type) VALUES (
						" . $this->GetIntProperty("company_unit_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("date_from") . ",
						" . $this->GetPropertyForSQL("date_to") . ",
						" . Connection::GetSQLString(INVOICE_STATUS_NEW) . ",
						" . $this->GetPropertyForSQL("invoice_type") . ")
					RETURNING invoice_id";
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        $this->SetProperty("invoice_id", $stmt->GetLastInsertID());
        self::SetInvoiceGUID($this->GetProperty("invoice_id"));

        $createdFrom = 'admin';

        //Save history
        $isCron = $this->GetProperty("is_cron");
        $invoiceId = $this->GetProperty("invoice_id");
        if ($isCron) {
            $userId = $this->GetProperty("invoice_type") == "invoice" ? SERVICE_RG : GUTSCHEIN_RG;
        } else {
            $user = new User();
            $user->LoadBySession();
            $userId = $user->GetIntProperty("user_id");
        }

        $values = "(" . $invoiceId . ",'archive','N','" . $userId . "','" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";

        $stmt1 = GetStatement(DB_CONTROL);
        $query = "INSERT INTO invoice_history (invoice_id, property_name, value, user_id, created, created_from) VALUES " . $values;

        $stmt1->Execute($query);

        return true;
    }

    /**
     * Generates and saves to db invoice_guid
     *
     * @param int $invoiceID invoice_id of invoice guid to be generated for
     */
    public static function SetInvoiceGUID($invoiceID)
    {
        $stmt = GetStatement();
        $query = "SELECT created FROM invoice WHERE invoice_id=" . intval($invoiceID);
        $created = $stmt->FetchField($query);

        $query = "SELECT invoice_type FROM invoice WHERE invoice_id=" . intval($invoiceID);
        $type = $stmt->FetchField($query);

        $guidPrefix = ($type == "voucher_invoice" ? "G" : "") . date("Ym", strtotime($created));
        $guidSuffix = 100000;

        $query = "SELECT MAX(invoice_guid) FROM invoice WHERE invoice_guid LIKE '" . Connection::GetSQLLike($guidPrefix) . "%' AND invoice_type=" . Connection::GetSQLString($type);
        $max = $stmt->FetchField($query);
        if ($max != null) {
            $guidSuffix = intval(preg_replace("/^" . $guidPrefix . "/", "", $max));
        }

        $invoiceGUID = $guidPrefix . ($guidSuffix + 1);

        $query = "UPDATE invoice SET invoice_guid=" . Connection::GetSQLString($invoiceGUID) . " 
					WHERE invoice_id=" . intval($invoiceID) . " AND invoice_guid IS NULL";

        return $stmt->Execute($query) ? $invoiceGUID : false;
    }

    /**
     * Generated pdf for invoice and outputs it to browser or to temporary local file
     *
     * @param Invoice $invoice invoice data
     * @param string $path if is not null then pdf will be saved to passed filepath instead of direct browser output
     * @param string $deactivationDate invoice deactivation date
     */
    function GenerateInvoicePDF(Invoice $invoice, $path = null, $deactivationDate = null)
    {
        //collect data for invoice template
        $data = $this->GetInvoiceData($invoice);

        $popupPage = new PopupPage($this->module);
        if ($invoice->GetProperty("invoice_type") == "voucher_invoice") {
            $content = $popupPage->Load("invoice_voucher_pdf.html");
            $content->SetVar(
                "VAT",
                Config::GetConfigValueByDate("voucher_invoice_vat", $invoice->GetProperty("date_from"))
            );
        } else {
            $content = $popupPage->Load("invoice_pdf.html");
            $content->SetVar("VAT", Config::GetConfigValueByDate("invoice_vat", $invoice->GetProperty("date_from")));
        }
        if (!empty($deactivationDate)) {
            $content->SetVar("DeactivationDate", $deactivationDate);
        }
        $content->SetVar("Module", $this->module);
        $content->LoadFromArray($data);
        $html = $popupPage->Grab($content);

        $pdf = new InvoicePDF($this->module, $data);
        $pdf->writeHTML($html, 2);

        if ($path === null) {
            $pdf->Output("invoice.pdf", "I");
            exit();
        }

        //PDF/A-3 generation was decided to temporarily roll back.
        //generate ZUGFeRD xml
        /*$xsi = "http://www.w3.org/2001/XMLSchema-instance";
        $ram = "urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:12";
        $udt = "urn:un:unece:uncefact:data:standard:UnqualifiedDataType:15";
        $rsm = "urn:ferd:CrossIndustryDocument:invoice:1p0";

        $xmlContent = array(
            "namespace" => $rsm,
            "SpecifiedExchangedDocumentContext" => array ("namespace" => $ram, "GuidelineSpecifiedDocumentContextParameter" => array ("namespace" => $ram, "ID" => "urn:ferd:CrossIndustryDocument:invoice:1p0:basic")),

            "HeaderExchangedDocument" => array ("namespace" => $ram, "ID" => $data["INVOICE_invoice_id"], "Name" => "RECHNUNG", "TypeCode" => "380",
                "IssueDateTime" => array ("namespace" => $udt, "attributes" => array("format" => "102"), "DateTimeString" => FormatDate("Ymd", $data["INVOICE_created"])),
                "LanguageID" => "de",
                "IncludedNote" => array("namespace" => $ram, "ContentCode" => "-", "Content" => "Rechnung für die gebuchten trebono Cloud Services für die Periode vom ".FormatDateGerman($data["INVOICE_date_from"])." bis ".FormatDateGerman($data["INVOICE_date_to"]), "SubjectCode" => "-")
            ),
            "SpecifiedSupplyChainTradeTransaction" => array ("namespace" => $ram, "ApplicableSupplyChainTradeAgreement" => array("namespace" => $ram,
                "SellerTradeParty" => array(
                    "namespace" => $ram, "ID" => CompanyUnit::GetPropertyValue("customer_guid", 2), "Name" => "2KS Cloud Services GmbH",
                    "DefinedTradeContact" => array ("namespace" => $ram, "PersonName" => "Thorsten Stein"),
                    "PostalTradeAddress" => array ("namespace" => $ram, "PostcodeCode" => "64367", "LineOne" => "Bahnhofstrasse 54", "CityName" => "Darmstadt - Mühltal", "CountryID" => "DE")
                ),
                "BuyerTradeParty" => array(
                    "namespace" => $ram, "ID" => $data["COMPANY_UNIT_customer_guid"], "Name" => "Bauer Engineering",
                    "PostalTradeAddress" => array ("namespace" => $ram, "PostcodeCode" => $data["COMPANY_UNIT_zip_code"], "LineOne" => $data["COMPANY_UNIT_street"]." ".$data["COMPANY_UNIT_house"], "CityName" => $data["COMPANY_UNIT_city"], "CountryID" => "DE")
                ),
            ),
                "ApplicableSupplyChainTradeDelivery" => array (
                    "namespace" => $ram, "ActualDeliverySupplyChainEvent" => array ("OccurrenceDateTime" => array(
                        "DateTimeString" => FormatDate("Ymd", $data["INVOICE_created"]), "namespace" => $udt, "attributes" => array("format" => "102")
                    )
                    )
                ),
                "ApplicableSupplyChainTradeSettlement" => array (
                    "namespace" => $ram, "PaymentReference" => $data["INVOICE_invoice_id"], "InvoiceCurrencyCode" => "EUR",
                    "SpecifiedTradeSettlementPaymentMeans" => array (
                        "namespace" => $ram, "Information" => "Zahlbar bis ".$data["INVOICE_payment_day"],
                        "PayeePartyCreditorFinancialAccount" => array ("namespace" => $ram, "IBANID" => $data["COMPANY_UNIT_iban"]),
                        "PayeeSpecifiedCreditorFinancialInstitution" => array ("namespace" => $ram, "BICID" => $data["COMPANY_UNIT_bic"])
                    ),
                    "SpecifiedTradeSettlementMonetarySummation" => array (
                        "namespace" => $ram, "attributes" => array("currencyID" => "EUR"), "LineTotalAmount" => $data["INVOICE_cost"], "ChargeTotalAmount" => $data["INVOICE_cost"],
                        "AllowanceTotalAmount" => 0, "TaxBasisTotalAmount" => $data["INVOICE_vat_amount"], "TaxTotalAmount" => $data["INVOICE_vat_amount"], "GrandTotalAmount" => $data["INVOICE_cost_with_vat"]
                    )
                )
            )
        );*/

        //this couldn't pass validation
        /*foreach ($data["COMPANY_UNIT_contact_list"] as $contact)
        {
            $xmlContent["SpecifiedSupplyChainTradeTransaction"]["ApplicableSupplyChainTradeAgreement"][] = array ("DefinedTradeContact" => array ("namespace" => $ram, "PersonName" => $contact["last_name"]." ".$contact["first_name"]));
        }*/
        /*foreach ($data["INVOICE_line_list"] as $line)
        {
            for ($i = 0; $i < count($line["OU_line_list"]); $i++)
            {
                $xmlContent[] = array(
                    "IncludedSupplyChainTradeLineItem" => array (
                        "namespace" => $ram,
                        "AssociatedDocumentLineDocument" => array ("LineID" => $i),
                        "SpecifiedSupplyChainTradeAgreement" => array ("namespace" => $ram,
                            "NetPriceProductTradePrice" => array ("namespace" => $ram, "attributes" => array("currencyID" => "EUR"), "ChargeAmount" => $line["OU_line_list"][$i]["cost"]
                            )
                        ),
                        "SpecifiedSupplyChainTradeDelivery" => array("namespace" => $ram, "BilledQuantity" => $line["OU_line_list"][$i]["quantity"], "attributes" => array("unitCode" => "C62")),
                        "SpecifiedSupplyChainTradeSettlement" => array (
                            "namespace" => $ram, "ApplicableTradeTax" => array ("namespace" => $ram, "TypeCode" => "VAT", "ApplicablePercent" => 19)
                        ),
                        "SpecifiedTradeProduct" => array (
                            "namespace" => $ram, "Name" => $line["OU_line_list"][$i]["product_title_translation"]
                        )
                    )
                );
            }
        }

        $xml = new SimpleXMLElement("<rsm:CrossIndustryDocument xmlns:xsi=\"$xsi\" xmlns:ram=\"$ram\" xmlns:udt=\"$udt\" xmlns:rsm=\"$rsm\"></rsm:CrossIndustryDocument>");
        $xml->registerXPathNamespace('ram', $ram);

        XmlContent($xml, $xmlContent, array(), $rsm);

        $fileStorage = GetFileStorage(CONTAINER__BILLING__INVOICE);
        $xmlPath = INVOICE_DIR;
        $xmlFileName = "ZUGFeRD-invoice.xml";
        $xml->asXML($xmlPath.$xmlFileName);

        $pdf->SetAssociatedFiles([[
            'name' => 'ZUGFeRD-invoice.xml',
            'mime' => 'text/xml',
            'description' => 'ZUGFeRD invoice',
            'AFRelationship' => 'Alternative',
            'path' => $xmlPath.$xmlFileName
        ]]);

        $fileStorage->MoveToStorage($xmlPath, PAYROLL_DIR, $xmlFileName);

        $rdf = '<rdf:Description xmlns:fx="urn:zugferd:pdfa:CrossIndustryDocument:invoice:2p0#"
                   fx:ConformanceLevel="EN 16931"
                   fx:DocumentFileName="ZUGFeRD-invoice.xml"
                   fx:DocumentType="INVOICE"
                   fx:Version="1.0"
                   rdf:about=""/>
                </rdf:RDF>';

        $pdf->SetAdditionalXmpRdf($rdf);*/
        //generate ZUGFeRD xml end

        $pdf->Output($path, "F");
    }

    /**
     * Sends email with generated invoice pdf to its company_unit email-address.
     * If email is sent successfully then invoice's status will be changed to INVOICE_STATUS_SENT
     *
     * @param int $invoiceID invoice_id of invoice to be sent
     */
    function Send($invoiceID)
    {
        $this->LoadByID($invoiceID);
        $year = date("Y", strtotime($this->GetProperty("date_from")));
        $month = date("m", strtotime($this->GetProperty("date_from")));
        $germanMonthName = GetGermanMonthName($month);
        $monthYear = $germanMonthName . " " . $year;

        $popupPage = new PopupPage($this->module);

        $pdfPath = PROJECT_DIR . "var/log/invoice_" . $this->GetProperty("invoice_guid") . ".pdf";
        $this->GenerateInvoicePDF($this, $pdfPath);

        $invoiceDetails = new InvoiceDetails($this->module);
        $detailsPdfPath = PROJECT_DIR . "var/log/invoice_details_" . $this->GetProperty("invoice_guid") . ".pdf";
        $invoiceDetails->GenerateInvoiceDetailsPDF($invoiceID, $detailsPdfPath);

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($this->GetProperty("company_unit_id"));
        $paymentTypeMap = array(
            "monthly" => "Monat",
            "quarterly" => "Quartal",
            "yearly" => "Jahr"
        );

        $fileName = date("ym") . "_" . $companyUnit->GetProperty("customer_guid") . "_" . $this->GetProperty("invoice_guid") . ".pdf";

        $fileStorage = GetFileStorage(CONTAINER__BILLING__INVOICE);
        $fileStorage->MoveToStorage($pdfPath, INVOICE_DIR, $fileName);

        $subject = $this->GetProperty("invoice_type") == "invoice"
            ? $this->GetProperty("company_unit_title") . ": Ihre Rechnung für den " . $paymentTypeMap[$companyUnit->GetProperty("payment_type")] . " " . $monthYear
            : $this->GetProperty("company_unit_title") . ": Ihre Gutschein Rechnung für den " . $paymentTypeMap[$companyUnit->GetProperty("payment_type")] . " " . $monthYear;

        $contactList = $this->GetInvoiceReceivers($this->GetProperty("company_unit_id"));

        $result = false;
        foreach ($contactList as $contact) {
            $content = $this->GetProperty("invoice_type") == "invoice"
                ? $popupPage->Load("invoice_email.html")
                : $popupPage->Load("invoice_voucher_email.html");

            $content->SetVar("company_unit_title", $companyUnit->GetProperty("title"));
            $content->LoadFromArray($this->GetInvoiceData($this));
            $content->LoadFromArray($contact);
            $attachments = [];

            if ($contact["sending_pdf_invoice"] == "Y") {
                $content->SetVar("Invoice_pdf", "Y");
                $attachments = [[
                    "File" => $fileStorage->GetFileContent(INVOICE_DIR . $fileName),
                    "FileName" => $fileName
                ]];
            }

            $html = $popupPage->Grab($content);

            $sendResult = SendMailFromAdmin(
                $contact["email"],
                $subject,
                $html,
                array(),
                array(array("Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo")),
                $attachments,
                "trebono Buchhaltung - 2KS Cloud Services GmbH"
            );
            if ($sendResult !== true) {
                continue;
            }

            $result = true;
        }

        if ($result === true) {
            $this->SetStatus($invoiceID, INVOICE_STATUS_SENT);
        } else {
            $this->SetStatus($invoiceID, INVOICE_STATUS_ERROR);
            $this->AddError($result);
            $result = false;
        }

        return $result;
    }

    /**
     * Collects invoice's properties and lines, its company_unit's properties and invoice contacts with prefixes to array usable in templates
     *
     * @param Invoice $invoice invoice data
     *
     * @return array $data
     */
    private function GetInvoiceData(Invoice $invoice)
    {
        $data = array();
        $companyUnit = new CompanyUnit("company");
        $companyUnitID = $invoice->GetProperty("company_unit_id");
        $companyUnit->LoadByID($companyUnitID);

        foreach ($invoice->GetProperties() as $key => $value) {
            $data["INVOICE_" . $key] = $value;
        }

        $year = date("Y", strtotime($invoice->GetProperty("date_from")));
        $month = date("m", strtotime($invoice->GetProperty("date_from")));
        $germanMonthName = GetGermanMonthName($month);
        $monthYear = $germanMonthName . " " . $year;
        $data["INVOICE_month_year"] = $monthYear;

        $paymentDay = new DateTime($invoice->GetProperty("created"));
        $paymentDay->modify("+5 day");
        $data["INVOICE_payment_day"] = $paymentDay->format("Y-m-d");

        $lineList = new InvoiceLineList($this->module);

        if ($invoice->GetProperty("invoice_id")) {
            $lineList->LoadInvoiceLineList($invoice->GetProperty("invoice_id"));
        } else {
            $lineList->LoadInvoiceLineListForPreview($invoice);
            $cost = array_sum(array_column($lineList->_items, "cost"));

            if ($invoice->GetProperty("invoice_type") == "voucher_invoice") {
                $vatAmount = round($cost * Config::GetConfigValueByDate(
                    "voucher_invoice_vat",
                    $invoice->GetProperty("date_from")
                ) / 100, 2);
            } else {
                $vatAmount = round($cost * Config::GetConfigValueByDate(
                    "invoice_vat",
                    $invoice->GetProperty("date_from")
                ) / 100, 2);
            }

            $data["INVOICE_cost"] = $cost;
            $data["INVOICE_vat_amount"] = $vatAmount;
            $data["INVOICE_cost_with_vat"] = $cost + $vatAmount;
        }

        $invoiceLineList = $lineList->GetItems();
        $data["INVOICE_line_list"] = array();
        foreach ($invoiceLineList as $line) {
            if (!isset($data['INVOICE_line_list'][$line['company_unit_id']])) {
                $data['INVOICE_line_list'][$line['company_unit_id']] = array(
                    'company_unit_id' => $line['company_unit_id'],
                    'SubUnitTitle' => CompanyUnit::GetPropertyValue("title", $line['company_unit_id']),
                    'OU_line_list' => array()
                );
            }
            if ($invoice->GetProperty('invoice_type') == 'voucher_invoice') {
                $line['max_monthly'] = $line['cost'] / $line['quantity'];
            }
            $data['INVOICE_line_list'][$line['company_unit_id']]['OU_line_list'][] = $line;
        }
        $data['INVOICE_line_list'] = array_values($data['INVOICE_line_list']);
        foreach ($data['INVOICE_line_list'] as $key => $unitData) {
            $data['INVOICE_line_list'][$key]['OU_line_list'][0]['show_type'] = 1;
            $data['INVOICE_line_list'][$key]['OU_cost'] = array_sum(array_column($unitData['OU_line_list'], "cost"));
        }

        foreach ($companyUnit->GetProperties() as $key => $value) {
            $data["COMPANY_UNIT_" . $key] = $value;
        }

        $paymentTypeMap = array(
            "monthly" => "Monat",
            "quarterly" => "Quartal",
            "yearly" => "Jahr"
        );
        $data["COMPANY_UNIT_payment_type_word"] = $paymentTypeMap[$companyUnit->GetProperty("payment_type")];

        $contactList = new ContactList("company");
        $contactList->LoadContactList($companyUnitID);

        $invoiceContactList = array();
        for ($i = 0; $i < $contactList->GetCountItems(); $i++) {
            if ($contactList->_items[$i]["contact_for_invoice"] != "Y") {
                continue;
            }

            $invoiceContactList[] = $contactList->_items[$i];
        }
        $data["COMPANY_UNIT_contact_list"] = $invoiceContactList;

        return $data;
    }

    /**
     * Returns receivers where invoice should be sent
     *
     * @param int $companyUnitID
     *
     * @return array
     */
    private function GetInvoiceReceivers($companyUnitID)
    {
        $invoiceContacts = [];

        $contactList = new ContactList("company");
        $contactList->LoadContactList($companyUnitID);
        $contactList = $contactList->GetItems();
        foreach ($contactList as $contact) {
            if ($contact["contact_for_invoice"] != "Y" || !$contact["email"]) {
                continue;
            }

            $invoiceContacts[] = $contact;
        }

        return $invoiceContacts;
    }

    /**
     * Updates status of invoice in DB
     *
     * @param int $invoiceID invoice_id of invoice status to be updated
     * @param string $status new invoice's status. Should be one of INVOICE_STATUS_* constants
     */
    public function SetStatus($invoiceID, $status)
    {
        $stmt = GetStatement();
        $query = "UPDATE invoice SET status=" . Connection::GetSQLString($status) . " WHERE invoice_id=" . intval($invoiceID);
        $stmt->Execute($query);
    }

    /**
     * Validates user invoice Role
     *
     * @param int $invoiceID receipt_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($invoiceID, $userID = null)
    {
        if (!$invoiceID) {
            return true;
        }

        $invoice = new Invoice("billing");
        $invoice->LoadByID($invoiceID);

        $permissionName = "invoice";

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

        return in_array($invoice->GetIntProperty("company_unit_id"), $companyUnitIDs) ? true : false;
    }

    /**
     * Returns properties value history for invoice
     *
     * @param array $property
     * @param int $invoiceID
     * @param bool $orderDesc
     *
     * @return array value properties
     */
    public static function GetPropertyValueListInvoice($property, $invoiceID, $orderDesc = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        $orderBy = $orderDesc ? " ORDER BY created DESC" : " ORDER BY created ASC";

        $query = "SELECT value_id, invoice_id, created, value, property_name, created_from, user_id
					FROM invoice_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND invoice_id=" . intval($invoiceID) . " 
					" . $orderBy;
        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];
        }

        for ($i = 0; $i < count($valueList); $i++) {
            if ($valueList[$i]["property_name"] == "archive") {
                $valueList[$i]["params_for_view_url"] = "Action=GetInvoicePDF&invoice_id=" .
                    $valueList[$i]["invoice_id"] . "&Archive=" . $valueList[$i]["value"] ;

                $valueList[$i]["value_text"] = $valueList[$i]["value"] == "N"
                    ? GetTranslation("Active")
                    : GetTranslation("Cancel");
            } else {
                $valueList[$i]["params_for_view_url"] = "Action=GetInvoicePDF&invoice_id=" . $valueList[$i]["invoice_id"];
            }
        }

        return $valueList;
    }

    /**
     * Checks if invoice for given date and company unit exists
     *
     * @param $companyUnitID
     * @param string $date
     * @param string $invoiceType
     *
     * @return bool true if invoice exists, false otherwise
     */
    public static function InvoiceExists($companyUnitID, $date, $invoiceType)
    {
        $stmt = GetStatement();
        $query = "SELECT invoice_id FROM invoice 
					WHERE company_unit_id=" . intval($companyUnitID) . " 
					AND archive='N' 
					AND invoice_type=" . Connection::GetSQLString($invoiceType) . "
					AND date_from<=" . Connection::GetSQLDate($date) . "
					AND date_to>=" . Connection::GetSQLDate($date);

        return boolval($stmt->FetchRow($query));
    }

    /**
     * Get invoice ids by date
     *
     * @param $companyUnitID
     * @param string $date
     *
     * @return array invoice ids
     */
    public function GetInvoiceIdsByDate($companyUnitID, $date)
    {
        $stmt = GetStatement();
        $query = "SELECT invoice_id FROM invoice 
					WHERE company_unit_id=" . intval($companyUnitID) . " 
					AND archive='N' 
					AND invoice_type='invoice'
					AND date_from<=" . Connection::GetSQLDate($date) . "
					AND date_to>=" . Connection::GetSQLDate($date);

        return $stmt->FetchRow($query);
    }
}
