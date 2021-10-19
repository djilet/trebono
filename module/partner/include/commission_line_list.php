<?php

class CommissionLineList extends LocalObjectList
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function CommissionLineList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "type_asc" => "l.date ASC",
            "type_desc" => "l.date DESC"
        ));
        $this->SetOrderBy("type_asc");
    }

    /**
     * Loads commission line list for partner view
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadCommissionLineList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }


        $where = array();
        $where[] = "l.partner_id=" . $request->GetIntProperty("partner_id");

        $query = "SELECT l.commission_line_id, l.partner_id, l.company_unit_id, l.product_id, l.type, l.value, l.date, l.revenue, 
						p.code AS product_code, 
						" . Connection::GetSQLDecryption("cu.title") . " AS company_unit						 
					FROM commission_line AS l 
						LEFT JOIN product AS p ON p.product_id=l.product_id 
						LEFT JOIN company_unit AS cu ON cu.company_unit_id=l.company_unit_id "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["product_title_translation"] = GetTranslation(
                "product-" . $this->_items[$i]["product_code"],
                "billing"
            );
            $this->_items[$i]["product_title_translation"] .= " " . GetTranslation(
                "commission-" . $this->_items[$i]["revenue"],
                "partner"
            );
        }
    }
}
