<?php

class OptionList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function OptionList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "created_asc" => "o.created ASC",
            "created_desc" => "o.created DESC",
            "group_sort_order_asc" => "g.sort_order ASC, o.sort_order ASC"
        ));
        $this->SetOrderBy("created_asc");
    }

    /**
     * Loads option list of global level
     */
    public function LoadOptionListForAdmin($productID, $optionLevel, $user = null)
    {
        $where = array();
        $where[] = "o.product_id=" . intval($productID);
        if ($optionLevel == OPTION_LEVEL_GLOBAL) {
            $where[] = "o.level_global='Y'";
        } elseif ($optionLevel == OPTION_LEVEL_COMPANY_UNIT) {
            $where[] = "o.level_company_unit='Y'";
        } elseif ($optionLevel == OPTION_LEVEL_EMPLOYEE) {
            $where[] = "o.level_employee='Y'";
        }

        $query = "SELECT o.option_id, o.type, o.code, o.title, o.group_id, o.product_id,
						g.title AS group_title, g.code AS group_code 
					FROM option AS o 
						LEFT JOIN option_group AS g ON g.group_id=o.group_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->SetOrderBy("group_sort_order_asc");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Returns fill list of available options for web API
     * @return array
     */
    public static function GetOptionListForWebApi()
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT code, title, product_id FROM option WHERE code NOT LIKE '%price' AND code NOT LIKE '%discount' ORDER BY product_id";
        $optiontList = $stmt->FetchList($query);

        $codeList = array();
        foreach ($optiontList as $option) {
            $productTranslation = GetTranslation("product-" . Product::GetProductCodeByID($option["product_id"]),
                "product");
            if (!is_array($codeList[$productTranslation])) {
                $codeList[$productTranslation] = array();
            }

            $codeList[$productTranslation][] = array(
                "Title" => GetTranslation("option-" . $option["code"], "product"),
                "Code" => $option["code"]
            );
        }

        return $codeList;
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["title_translation"] = GetTranslation("option-" . $this->_items[$i]["code"],
                $this->module);
            $this->_items[$i]["group_title_translation"] = GetTranslation("option-group-" . $this->_items[$i]["group_code"],
                $this->module);

            if ($i == 0 || $this->_items[$i]["group_id"] != $this->_items[$i - 1]["group_id"]) {
                $this->_items[$i]["show_group"] = 1;
            }

            if (!isset($this->_items[$i]["value"])) {
                $this->_items[$i]["value"] = null;
            }

            if ($this->_items[$i]["code"] == OPTION__TRAVEL__MAIN__CREDITOR_BOOKING && $this->_items[$i]["value"] == null) {
                $this->_items[$i]["value"] = "Y";
            }
        }
    }
}

?>