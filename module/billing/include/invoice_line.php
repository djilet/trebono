<?php

class InvoiceLine extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of invoice line properties to be loaded instantly
     */
    public function InvoiceLine($module, $data = array())
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
        $query = "INSERT INTO invoice_line (invoice_id, product_id, type, quantity, cost, company_unit_id,
                    flex_quantity, flex_unit_count, flex_unit_price, flex_unit_sum, flex_amount_sum, flex_unit_percentage, flex_percentage_sum, billable_item_id) 
                    VALUES (
						" . $this->GetIntProperty("invoice_id") . ",
						" . $this->GetIntProperty("product_id") . ",
						" . $this->GetPropertyForSQL("type") . ",
						" . $this->GetIntProperty("quantity") . ",
						" . $this->GetFloatProperty("cost") . ", 
						" . $this->GetFloatProperty("company_unit_id") . ",
						" . $this->GetIntProperty("flex_quantity") . ",
						" . $this->GetIntProperty("flex_unit_count") . ",
						" . $this->GetPropertyForSQL("flex_unit_price") . ",
						" . $this->GetIntProperty("flex_unit_sum") . ",
						" . $this->GetFloatProperty("flex_amount_sum") . ", 
						" . $this->GetFloatProperty("flex_unit_percentage") . ",
						" . $this->GetFloatProperty("flex_percentage_sum") . ",
						" . $this->GetIntProperty("billable_item_id") . ")
					RETURNING invoice_line_id";
        if ($stmt->Execute($query)) {
            $this->SetProperty("invoice_line_id", $stmt->GetLastInsertID());

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads the list of existing invoice lines by specified parameters.
     *
     * @param int $company_unit_id - id of Invoice company unit
     * @param int $product_id - id of specified product
     * @param string $dateFrom - start date of Invoice
     * @param string $dateTo - end date of Invoice
     * @param string $invoiceType - type of invoice - "invoice" for regular invoices, "voucher_invoice" for additional invoice
     *
     * @return array|bool array of invoice lines indexed by type if exist or false otherwise.
     */
    public static function CheckLine($company_unit_id, $product_id, $dateFrom, $dateTo, $invoiceType = "invoice")
    {
        if (empty($product_id)) {
            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT il.*,
                        COALESCE(SUM(cost +
                               COALESCE(flex_unit_sum::numeric, 0) +
                               COALESCE(flex_percentage_sum::numeric, 0)
                        ), 0) AS flex_cost
                    FROM invoice_line il LEFT JOIN invoice i ON il.invoice_id=i.invoice_id
                    WHERE il.product_id=" . intval($product_id) . " AND il.company_unit_id=" . intval($company_unit_id) . " AND i.archive!='Y'
                        AND i.date_from=" . Connection::GetSQLDate($dateFrom) . " AND i.date_to=" . Connection::GetSQLDate($dateTo) . " AND i.invoice_type=" . Connection::GetSQLString($invoiceType) ."
                    GROUP BY il.invoice_line_id";

        return $stmt->FetchList($query);
    }
}
