<?php

class MasterDataList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function MasterDataList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "date_asc" => "created ASC",
            "date_desc" => "created DESC"
        ));
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads master data list
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadMasterDataList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $query = "SELECT master_data_id, type, created, new
                  	FROM master_data";

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
            $update = $this->_items[$i]["new"] == "N" ? "_update" : "";
            $this->_items[$i]["type_title"] = GetTranslation(
                $this->_items[$i]["type"] . $update . "-type",
                $this->module
            );
        }
    }
}
