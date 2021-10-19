<?php

class ReceiptLineList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ReceiptLineList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "line_number_asc" => "l.line_number ASC, l.created ASC",
            "line_number_desc" => "l.line_number DESC, l.created DESC",
            "line_id_asc" => "l.line_id ASC, l.created ASC",
            "line_id_desc" => "l.line_id DESC, l.created DESC",
        ));
        $this->SetOrderBy("line_id_asc");
    }

    /**
     * Loads receipt line list
     *
     * @param int $receiptID receipt_id line list to be filtered by
     */
    public function LoadLineList($receiptID)
    {
        $where = array();
        $where[] = "l.receipt_id=" . intval($receiptID);

        $query = "SELECT l.line_id, l.receipt_id, l.created, l.line_number, l.sku, l.title, l.quantity, l.price, round(l.quantity * l.price, 2) AS cost, l.vat, l.approved
					FROM receipt_line AS l 
					" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareBeforeShow()
    {
        //product title preparing callback
        $callback = static function ($str) {
            $str = trim($str);
            $str = mb_strtolower($str, "utf-8");

            return $str;
        };

        //prepare vat exceptions
        $vat7exceptionsShop = Config::GetConfigValue(Config::CODE_VAT_EXCEPTION_7_SHOP);
        $vat7exceptionsShop = preg_split('/\r\n|\r|\n/', $vat7exceptionsShop);
        $vat7exceptionsShop = array_map($callback, $vat7exceptionsShop);

        $vat19exceptionsShop = Config::GetConfigValue(Config::CODE_VAT_EXCEPTION_19_SHOP);
        $vat19exceptionsShop = preg_split('/\r\n|\r|\n/', $vat19exceptionsShop);
        $vat19exceptionsShop = array_map($callback, $vat19exceptionsShop);

        $exceptionsRestaurant = Config::GetConfigValue(Config::CODE_VAT_EXCEPTION_RESTAURANT);
        $exceptionsRestaurant = preg_split('/\r\n|\r|\n/', $exceptionsRestaurant);
        $exceptionsRestaurant = array_map($callback, $exceptionsRestaurant);

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $receiptFrom = Receipt::GetReceiptFromByID($this->_items[$i]["receipt_id"]);
            if ($receiptFrom == "shop") {
                $preparedTitle = $callback($this->_items[$i]["title"]);
                $approvable = null;
                if ($this->_items[$i]["vat"] == 7) {
                    $approvable = in_array($preparedTitle, $vat7exceptionsShop) ? "N" : "Y";
                } elseif ($this->_items[$i]["vat"] == 19) {
                    $approvable = in_array($preparedTitle, $vat19exceptionsShop) ? "Y" : "N";
                }
                $this->_items[$i]["approvable"] = $approvable;
            } elseif ($receiptFrom == "restaurant") {
                $preparedTitle = $callback($this->_items[$i]["title"]);
                $approvable = null;
                $approvable = in_array($preparedTitle, $exceptionsRestaurant) ? "N" : "Y";

                $this->_items[$i]["approvable"] = $approvable;
            }
        }
    }

    /**
     * Removes receipt lines from database by provided ids.
     *
     * @param array $ids array of line_id's
     */
    public function Remove($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $receiptIDs = array_keys($stmt->FetchIndexedList("SELECT DISTINCT(receipt_id) FROM receipt_line WHERE line_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")"));
        if (count($receiptIDs) > 0) {
            foreach ($receiptIDs as $receiptID) {
                Receipt::Touch($receiptID);
            }
        }

        $query = "DELETE FROM receipt_line WHERE line_id IN (" . implode(", ", Connection::GetSQLArray($ids)) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }

    /**
     * Change flag approve to Y or N
     *
     * @param array $ids array of line_id's
     * @param string $approve approved line or not
     */

    public function Approve($ids, $approved)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $receiptIDs = array_keys($stmt->FetchIndexedList("SELECT DISTINCT(receipt_id) FROM receipt_line WHERE line_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")"));
        if (count($receiptIDs) > 0) {
            foreach ($receiptIDs as $receiptID) {
                Receipt::Touch($receiptID);
            }
        }

        $query = "UPDATE receipt_line SET approved=" . Connection::GetSQLString($approved) . " WHERE line_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);
    }
}
