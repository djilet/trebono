<?php

class InvoiceDetails extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of invoice line properties to be loaded instantly
     */
    public function InvoiceDetails($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Creates new invoice line. Object must be loaded by properties before the method will be called.
     * Required properties are: invoice_id, product_id, type, quantity, cost
     *
     * @return bool true if invoice line is created successfully or false on failure
     */
    public function Create()
    {
        $stmt = GetStatement();
        if ($this->GetProperty("invoice_type") == "voucher_invoice") {
            $voucherIDs = json_encode($this->GetProperty("voucher_list"));
            $query = "INSERT INTO invoice_details (invoice_id, employee_id, product_id, type, voucher_ids, company_unit_id) VALUES (
						" . $this->GetIntProperty("invoice_id") . ",
						" . $this->GetIntProperty("employee_id") . ",
						" . $this->GetIntProperty("product_id") . ",
						" . $this->GetPropertyForSQL("type") . ",
						" . Connection::GetSQLString($voucherIDs) . ", 
						" . $this->GetFloatProperty("company_unit_id") . ")
					RETURNING invoice_details_id";
        } else {
            $query = "INSERT INTO invoice_details (invoice_id, employee_id, product_id, type, days_count, cost, company_unit_id,
                        flex_employee_units, flex_free_units, flex_unit_count, flex_unit_price, flex_unit_sum, flex_unit, flex_amount_sum, flex_unit_percentage, flex_percentage_sum) 
                        VALUES (
						" . $this->GetIntProperty("invoice_id") . ",
						" . $this->GetIntProperty("employee_id") . ",
						" . $this->GetIntProperty("product_id") . ",
						" . $this->GetPropertyForSQL("type") . ",
						" . $this->GetIntProperty("days_count") . ",
						" . $this->GetFloatProperty("cost") . ", 
						" . $this->GetFloatProperty("company_unit_id") . ",
						" . $this->GetIntProperty("flex_employee_units") . ",
						" . $this->GetIntProperty("flex_free_units") . ",
						" . $this->GetIntProperty("flex_unit_count") . ",
						" . $this->GetPropertyForSQL("flex_unit_price") . ",
						" . $this->GetIntProperty("flex_unit_sum") . ",
						" . $this->GetFloatProperty("flex_unit") . ", 
						" . $this->GetFloatProperty("flex_amount_sum") . ",
						" . $this->GetFloatProperty("flex_unit_percentage") . ", 
						" . $this->GetFloatProperty("flex_percentage_sum") . ")
					RETURNING invoice_details_id";
        }

        if ($stmt->Execute($query)) {
            $this->SetProperty("invoice_details_id", $stmt->GetLastInsertID());

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    public static function CheckDetails($invoiceID, $types)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT *, COALESCE(SUM(cost +
                               COALESCE(flex_unit_sum::numeric, 0) +
                               COALESCE(flex_percentage_sum::numeric, 0)
                        ), 0) AS flex_cost
                    FROM invoice_details
                    WHERE invoice_id=" . intval($invoiceID) . " AND type IN ('" . implode("','", $types) . "')
                    GROUP BY invoice_details_id";

        return $stmt->FetchList($query);
    }

    public function GenerateInvoiceDetailsPDF($invoiceID, $pdfPath)
    {
        $popupPage = new PopupPage($this->module);
        $content = $popupPage->Load("invoice_details_pdf.html");

        $invoice = new Invoice($this->module);
        $invoice->LoadByID($invoiceID);
        $dateFrom = $invoice->GetProperty("date_from");
        $dateTo = $invoice->GetProperty("date_to");
        $companyUnitID = $invoice->GetProperty("company_unit_id");

        $stmt = GetStatement();
        $query = "SELECT * FROM invoice_details WHERE invoice_id=" . Connection::GetSQLString($invoiceID);
        $tmpDetailsList = $stmt->FetchList($query);

        $detailsList = array();
        $cost = 0;
        for ($i = 0; $i < count($tmpDetailsList); $i++) {
            $productCode = Product::GetProductCodeByID($tmpDetailsList[$i]["product_id"]);
            $tmpDetailsList[$i]["employee_name"] = Employee::GetNameByID($tmpDetailsList[$i]["employee_id"]);
            $tmpDetailsList[$i]["date_from"] = $dateFrom;
            $tmpDetailsList[$i]["date_to"] = $dateTo;
            $tmpDetailsList[$i]["flex_cost"] = 0;

            if ($invoice->GetProperty("invoice_type") == "voucher_invoice") {
                $tmpDetailsList[$i]["voucher_ids"] = json_decode($tmpDetailsList[$i]["voucher_ids"], true);
                $tmpDetailsList[$i]["cost"] = 0;
                foreach ($tmpDetailsList[$i]["voucher_ids"] as $voucher) {
                    $tmpDetailsList[$i]["cost"] += $voucher["amount"];
                }
            }

            $companyUnitKey = array_search(
                $tmpDetailsList[$i]["company_unit_id"],
                array_column($detailsList, "company_unit_id")
            );
            if ($companyUnitKey === false) {
                $detailsList[] = array(
                    "company_unit_id" => $tmpDetailsList[$i]["company_unit_id"],
                    "company_unit_title" => CompanyUnit::GetTitleByID($tmpDetailsList[$i]["company_unit_id"]),
                    "price" => 0
                );
                $companyUnitKey = count($detailsList) - 1;
            }

            if ($tmpDetailsList[$i]["type"] == "implementation") {
                if (!isset($detailsList[$companyUnitKey]["implementation"])) {
                    $detailsList[$companyUnitKey]["implementation"] = array(array("product_list" => array()));
                }

                $tmpDetailsList[$i]["user_per_month"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $productCode . "__implementation_price",
                    $companyUnitID,
                    $dateTo
                );
                $tmpDetailsList[$i]["discount"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $productCode . "__implementation_discount",
                    $companyUnitID,
                    $dateTo
                );

                $key = array_search(
                    $tmpDetailsList[$i]["product_id"],
                    array_column($detailsList[$companyUnitKey]["implementation"][0]["product_list"], "product_id")
                );
                if ($key === false) {
                    $detailsList[$companyUnitKey]["implementation"][0]["product_list"][] = array(
                        "product_id" => $tmpDetailsList[$i]["product_id"],
                        "product_title_translation" => GetTranslation(
                            "product-" . Product::GetProductCodeByID($tmpDetailsList[$i]["product_id"]),
                            "billing"
                        )
                    );
                    $key = count($detailsList[$companyUnitKey]["implementation"][0]["product_list"]) - 1;
                }

                $detailsList[$companyUnitKey]["implementation"][0]["product_list"][$key]["details_array"][] = $tmpDetailsList[$i];
            } else {
                if (!isset($detailsList[$companyUnitKey]["recurring"])) {
                    $detailsList[$companyUnitKey]["recurring"] = array(array("product_list" => array()));
                }

                if ($tmpDetailsList[$i]["type"] == "recurring_flex") {
                    $tmpDetailsList[$i]["product_id"] .= "_flex";
                    $productTitle = GetTranslation("product_flex-" . $productCode, "billing");
                    $tmpDetailsList[$i]["flex"] = 1;
                    $tmpDetailsList[$i]["flex_unit_percentage_calculation"] = $tmpDetailsList[$i]["flex_unit_percentage"] / 100;
                    $tmpDetailsList[$i]["flex_cost"] = $tmpDetailsList[$i]["flex_unit_sum"] + $tmpDetailsList[$i]["flex_percentage_sum"];
                } else {
                    $productTitle = GetTranslation("product-" . $productCode, "billing");
                }

                $specificProduct = SpecificProductFactory::Create($productCode);
                $tmpDetailsList[$i]["user_per_month"] = $specificProduct->GetMonthlyPrice($companyUnitID, $dateTo);
                $tmpDetailsList[$i]["discount"] = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $productCode . "__monthly_discount",
                    $companyUnitID,
                    $dateTo
                );

                $key = array_search(
                    $tmpDetailsList[$i]["product_id"],
                    array_column($detailsList[$companyUnitKey]["recurring"][0]["product_list"], "product_id")
                );
                if ($key === false) {
                    $detailsList[$companyUnitKey]["recurring"][0]["product_list"][] = array(
                        "product_id" => $tmpDetailsList[$i]["product_id"],
                        "product_title_translation" => $productTitle,
                        "product_translation_unit_count_flex" => GetTranslation(
                            "product_flex_unit_count-" . $productCode,
                            "billing"
                        ),
                        "product_translation_total_amount_flex" => GetTranslation(
                            "product_flex_total_amount-" . $productCode,
                            "billing"
                        ),
                    );
                    $key = count($detailsList[$companyUnitKey]["recurring"][0]["product_list"]) - 1;
                }

                $detailsList[$companyUnitKey]["recurring"][0]["product_list"][$key]["details_array"][] = $tmpDetailsList[$i];
            }
            $detailsList[$companyUnitKey]["price"] += $tmpDetailsList[$i]["cost"] + $tmpDetailsList[$i]["flex_cost"];
            $cost += $tmpDetailsList[$i]["cost"] + $tmpDetailsList[$i]["flex_cost"];
        }

        if (count($detailsList) <= 0) {
            return;
        }

        $vat = $invoice->GetProperty("invoice_type") == "voucher_invoice" ? Config::GetConfigValueByDate("voucher_invoice_vat", $dateFrom) : Config::GetConfigValueByDate("invoice_vat", $dateFrom);

        $vatAmount = round($cost * $vat / 100, 2);
        $content->SetVar("VAT", $vat);

        $content->SetVar("cost", $cost);
        $content->SetVar("vat_amount", $vatAmount);
        $content->SetVar("cost_with_vat", $cost + $vatAmount);

        $content->SetVar("invoice_guid", $invoice->GetProperty("invoice_guid"));
        $content->SetVar("invoice_type", $invoice->GetProperty("invoice_type"));

        $content->LoadFromArray(array("DetailsList" => $detailsList));
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
            'tempDir' => INVOICE_TMP_DIR,
            'orientation' => 'P',
        ]);
        $pdf->PDFA = true;
        $pdf->PDFAauto = true;
        $css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/invoice_pdf_style.css");
        $pdf->simpleTables = true;
        $pdf->packTableData = true;

        $pdf->WriteHTML($css, 1);
        $pdf->debug = true;

        $htmlLength = strlen($html);
        if ($htmlLength > 1000000) {
            //bad solution, but in case it's a huge pdf remove limit on memory and break file into chunks
            ini_set('memory_limit', -1);
            $pdf->packTableData = true;
            $pdf->simpleTables = true;
            $chunks = explode("<!-- chunk -->", $html);
            foreach ($chunks as $key => $chunk) {
                $pdf->WriteHTML($chunk, 2);
            }
        } else {
            $pdf->WriteHTML($html, 2);
        }

        $pdf->Output($pdfPath, "F");
        $fileName = "details_" . date("ym") . "_" . CompanyUnit::GetPropertyValue(
            "customer_guid",
            $companyUnitID
        ) . "_" . $invoice->GetProperty("invoice_guid") . ".pdf";

        $fileStorage = GetFileStorage(CONTAINER__BILLING__INVOICE);
        $fileStorage->MoveToStorage($pdfPath, INVOICE_DIR, $fileName);

        $query = "UPDATE invoice SET details_file=" . Connection::GetSQLString($fileName) . " WHERE invoice_id=" . Connection::GetSQLString($invoiceID);
        $stmt->Execute($query);
    }
}
