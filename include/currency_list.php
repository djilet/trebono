<?php

class CurrencyList extends LocalObjectList
{
    var $module;
    var $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function CurrencyList($data = array())
    {
        parent::LocalObjectList($data);

        $this->SetSortOrderFields(array(
            "id_asc" => "currency_id ASC",
            "id_desc" => "currency_id DESC",
            "title_asc" => "title ASC, currency_id ASC",
            "title_desc" => "title DESC, currency_id DESC",
            "digit_asc" => "digit ASC, currency_id ASC",
            "digit_desc" => "digit DESC, currency_id DESC",
        ));
        $this->SetOrderBy("digit_asc");
        $this->SetItemsOnPage(0);
    }

    /**
     * @param int $selectedID id of selected currency
 * Loads currency list
     */
    public function LoadCurrencyList($selectedID)
    {
        $query = "SELECT currency_id AS id, digit, title FROM currency";
        $this->LoadFromSQL($query, GetStatement(DB_MAIN));
        $this->PrepareBeforeShow($selectedID);
    }

    /**
     * @param int $selectedID id of selected currency
 * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareBeforeShow($selectedID)
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            if (intval($selectedID) > 0 || $this->_items[$i]["digit"] != "EUR" && intval($selectedID) <= 0 || $this->_items[$i]["id"] != $selectedID) {
                continue;
            }

            $this->_items[$i]["selected"] = 1;
        }
    }
}
