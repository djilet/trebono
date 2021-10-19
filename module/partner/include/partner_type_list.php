<?php

/**
 * User: der
 * Date: 17.09.18
 * Time: 18:12
 */

class PartnerTypeList extends LocalObjectList
{

    /**
     * Constructor
     *
     * @param array $data Array of items to be loaded instantly
     */
    public function PartnerTypeList($data = array())
    {
        parent::LocalObjectList($data);
    }

    /**
     * Loads full partner type list for admin panel
     */
    public function LoadPartnerTypeList()
    {
        $query = "SELECT * FROM partner_type";
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            if ($this->_items[$i]["long"]) {
                continue;
            }

            $this->_items[$i]["long"] = "&infin;";
        }
    }
}
