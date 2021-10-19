<?php

class AgreementEmployeeList extends LocalObjectList
{
    private $module;

    /**
     * AgreementEmployeeList constructor.
     *
     * @param $module
     */
    public function __construct($module, $data = [])
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields([
            "version_asc" => "version ASC",
            "version_desc" => "version DESC",
        ]);
        $this->SetOrderBy("version_desc");
        $this->SetItemsOnPage(10);
    }


    public function LoadByEmployeeID(Employee $employee, $groupId)
    {
        $where = [
            "a.group_id=" . intval($groupId),
            "a.organization_id=" . $employee->GetIntProperty("company_unit_id"),
            "e.employee_id=" . $employee->GetIntProperty("employee_id"),
        ];

        $query = "SELECT a.agreement_id, a.group_id, a.organization_id, e.*
            FROM agreements AS a
            INNER JOIN agreements_employee AS e ON a.agreement_id=e.agreement_id 
            WHERE " . implode(" AND ", $where);
        $this->LoadFromSQL($query);

        $this->PrepareBeforeShow();
    }

    private function PrepareBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            if (intval($this->_items[$i]["organization_id"]) <= 0) {
                continue;
            }

            $this->_items[$i]["linkToAcceptedAgreementVersion"] = ADMIN_PATH . "module.php?load=" . $this->module .
                "&OrganizationID=" . $this->_items[$i]["organization_id"] .
                "&AgreementID=" . $this->_items[$i]["agreement_id"] .
                "&Version=" . $this->_items[$i]["version"];
        }
    }
}
