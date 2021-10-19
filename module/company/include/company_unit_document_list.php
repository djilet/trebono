<?php

class CompanyUnitDocumentList extends LocalObjectList
{
    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function CompanyUnitDocumentList($data = array())
    {
        parent::LocalObjectList($data);

        $this->SetSortOrderFields(array(
            "title_asc" => "archive DESC, title ASC, document_id ASC",
            "title_desc" => "archive DESC, title DESC, document_id DESC"
        ));
        $this->SetOrderBy("title_asc");
        $this->SetItemsOnPage(10);
    }

    public function LoadCompanyUnitDocumentList($request)
    {
        $query = "SELECT * FROM company_unit_document WHERE company_unit_id=" . $request->GetPropertyForSQL("company_unit_id");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_MAIN));
        $this->PrepareContentBeforeShow();
    }

    private function PrepareContentBeforeShow()
    {
    }
}
