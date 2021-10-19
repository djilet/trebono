<?php

class AgreementEmployee extends LocalObject
{
    private $module;

    /**
     * AgreementEmployee constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }


    public function LoadForEmployeeID(Employee $employee, $groupId)
    {
        $where = [
            "a.group_id=" . intval($groupId),
            "a.organization_id=" . $employee->GetIntProperty("company_unit_id"),
        ];

        $query = "SELECT a.agreement_id, a.group_id, a.organization_id, e.*
            FROM agreements AS a
            INNER JOIN (
                SELECT DISTINCT ON(e.agreement_id, e.employee_id) e.* 
                FROM agreements_employee AS e ORDER BY e.agreement_id, e.employee_id, e.version DESC
            ) AS e ON a.agreement_id=e.agreement_id AND e.employee_id=" . $employee->GetIntProperty("employee_id") . " 
            WHERE " . implode(" AND ", $where);

        $this->LoadFromSQL($query);
        $this->Prepare();
    }

    private function Prepare()
    {
        if ($this->GetIntProperty("organization_id") <= 0) {
            return;
        }

        $this->_properties["linkToAcceptedAgreementVersion"] = ADMIN_PATH . "module.php?load=" . $this->module .
            "&OrganizationID=" . $this->GetIntProperty("organization_id") .
            "&AgreementID=" . $this->GetIntProperty("agreement_id") .
            "&Version=" . $this->GetIntProperty("version");
    }
}
