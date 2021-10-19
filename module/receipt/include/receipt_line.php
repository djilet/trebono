<?php

class ReceiptLine extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of receipt properties to be loaded instantly
     */
    public function ReceiptLine($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads receipt line by its line_id
     *
     * @param int $id line_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT l.line_id, l.receipt_id, l.created, l.line_number, l.sku, l.title, l.quantity, l.price, l.vat 
					FROM receipt_line AS l 
					WHERE l.line_id=" . intval($id);
        $this->LoadFromSQL($query);

        return $this->GetProperty("line_id") ? true : false;
    }

    /**
     * Creates or updates the receipt line. Object must be loaded from request before the method will be called.
     * Required properties are: receipt_id (only for creation), title, quantity, price
     *
     * @return bool true if receipt line is created/updated successfully or false on failure
     */
    public function Save()
    {
        $receipt = new Receipt($this->module);
        $receipt->LoadByID($this->GetIntProperty("receipt_id"));

        $group = new ProductGroup('product');
        $group->LoadByID($receipt->GetIntProperty('group_id'));

        if ($receipt->GetProperty("datev_export") != 0) {
            return true;
        }

        if (!$this->Validate()) {
            return false;
        }

        $price = $this->GetProperty("price");
        $price = preg_match("/[,]/", $price) ? preg_replace("/[^0-9,]/", "", $price) : preg_replace("/[^0-9.]/", "", $price);
        $price = str_replace(",", ".", $price);
        $price = floatval($price);
        $stmt = GetStatement();

        $query = $this->GetIntProperty("line_id") > 0 ? "UPDATE receipt_line SET
						line_number=" . $this->GetIntProperty("line_number") . ",
						sku=" . $this->GetPropertyForSQL("sku") . ",
						title=" . $this->GetPropertyForSQL("title") . ", 
						quantity=" . $this->GetFloatProperty("quantity") . ", 
						price=" . $price . ",
                        vat=" . $this->GetPropertyForSQL("vat") . "  
					WHERE line_id=" . $this->GetIntProperty("line_id") : "INSERT INTO receipt_line (receipt_id, created, line_number, sku, title, quantity, price, vat) VALUES (
						" . $this->GetIntProperty("receipt_id") . ", 						
						" . Connection::GetSQLString(GetCurrentDateTime()) . ", 						
						" . $this->GetIntProperty("line_number") . ",
						" . $this->GetPropertyForSQL("sku") . ",						
						" . $this->GetPropertyForSQL("title") . ",
						" . $this->GetFloatProperty("quantity") . ",
						" . $price . ",
                        " . $this->GetPropertyForSQL("vat") . ")  
					RETURNING line_id";

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("line_id") > 0) {
            $this->SetProperty("line_id", $stmt->GetLastInsertID());
        }

        Receipt::Touch($this->GetProperty("receipt_id"));

        return true;
    }

    /**
     * Validates input data when trying to create/update receipt line.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        $receipt = new Receipt($this->module);
        $receipt->LoadByID($this->GetIntProperty('receipt_id'));

        if (!$this->ValidateNotEmpty("title")) {
            $this->AddError("receipt-line-title-empty", $this->module);
        }

        $quantity = str_replace(".", "", $this->GetProperty("quantity"));
        $quantity = str_replace(",", ".", $quantity);
        if (!is_numeric($quantity) || $this->GetFloatProperty("quantity") <= 0) {
            $this->AddError("receipt-line-count-incorrect", $this->module);
        }

        $price = str_replace(".", "", $this->GetProperty("price"));
        $price = str_replace(",", ".", $price);
        if (!is_numeric($price) || $this->GetFloatProperty("price") <= 0) {
            $this->AddError("receipt-line-price-incorrect", $this->module);
        }

        return !$this->HasErrors();
    }

    /**
     * Return title of receipt line by id.
     *
     * @param int $id of required line
     *
     * @return $title
     */
    static function GetTitleByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT title FROM receipt_line WHERE line_id=" . intval($id);

        return $stmt->FetchField($query);
    }
}
