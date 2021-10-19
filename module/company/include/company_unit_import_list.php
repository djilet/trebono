<?php

class CompanyUnitImportList extends LocalObjectList
{
    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function CompanyUnitImportList($data = array())
    {
        parent::LocalObjectList($data);

        $this->SetSortOrderFields(array(
            "date_asc" => "created ASC",
            "date_desc" => "created DESC"
        ));
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(20);
    }

    /**
     * Loads operation list using filter params
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *      <li><u>FilterDateRange</u> - string - property for "date" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterUser</u> - string - property for user name filtration</li>
     *      <li><u>FilterSection</u> - string - property for section name filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadCompanyUnitImportList($request)
    {
        $where = array();
        //process filter params
        if ($request->GetProperty("FilterDateRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterDateRange"));
            $where[] = "created >= " . Connection::GetSQLDateTime($from);
            $where[] = "created <= " . Connection::GetSQLDateTime($to);
        }

        $query = "SELECT * FROM import_company_unit_history";
        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();
    }

    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $user = new User();
            if ($user->LoadByID($this->_items[$i]["user_id"])) {
            }
            {
                $this->_items[$i]["first_name"] = $user->GetProperty("first_name");
                $this->_items[$i]["last_name"] = $user->GetProperty("last_name");
            }

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($this->_items[$i]["company_unit_id"]);
            $this->_items[$i]["company_unit_title"] = $companyUnit->GetProperty("title");
        }
    }
}
