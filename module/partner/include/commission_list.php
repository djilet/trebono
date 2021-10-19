<?php

class CommissionList extends LocalObjectList
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function CommissionList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "type_asc" => "l.date ASC",
            "type_desc" => "l.date DESC"
        ));
        $this->SetOrderBy("type_asc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads commission list for partner view
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadCommissionList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }


        $where = array();
        $where[] = "l.partner_id=" . $request->GetIntProperty("partner_id");

        $query = "SELECT SUM(l.value) as sum, l.date, p.title 					  						 
					FROM commission_line AS l
					LEFT JOIN partner AS p ON p.\"PartnerID\"=l.partner_id 					  
                  WHERE l.partner_id=" . $request->GetIntProperty("partner_id") . "
                  GROUP BY l.date, p.title";
        $this->SetCurrentPage();
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]['ExportFile'] = date_create($this->_items[$i]['date'])->format("Y-m-d") . "-" . str_replace(
                " ",
                "_",
                $this->_items[$i]["title"]
            ) . "-report.xlsx";
            PrepareDownloadPath($this->_items[$i], 'ExportFile', REPORT_DIR, CONTAINER__PARTNER);
        }
    }
}
