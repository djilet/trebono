<?php

class AgreementsEmployeesList extends LocalObjectList
{
    private $module;

    /**
     * AgreementsEmployeesList constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }


    /**
     * Load users with agreement information
     *
     * @param int $organizationID Company unit id
     */
    public function LoadAll($organizationID)
    {
        $stmt = GetStatement();
        $stmt_personal = GetStatement(DB_PERSONAL);

        $query = "SELECT u.user_id, e.employee_id, 
				" . Connection::GetSQLDecryption("u.first_name") . " AS first_name, 
				" . Connection::GetSQLDecryption("u.last_name") . " AS last_name
            FROM employee AS e
            	INNER JOIN user_info AS u ON e.user_id=u.user_id
            WHERE e.company_unit_id=" . intval($organizationID) . "
            ORDER BY " . Connection::GetSQLDecryption("u.last_name") . " ASC, " .
            Connection::GetSQLDecryption("u.first_name") . " ASC";
        $list = $stmt_personal->FetchList($query);

        foreach ($list as $key => $user) {
            $query = "SELECT DISTINCT ON(t.agreement_id, t.employee_id) t.*, a.version AS last_version, 
                    g.code AS group_code
                FROM agreements_employee as t
                INNER JOIN agreements AS a ON t.agreement_id=a.agreement_id
                INNER JOIN product_group AS g ON a.group_id=g.group_id
                WHERE t.employee_id=" . intval($user["employee_id"]) . "
                ORDER BY t.employee_id ASC, t.agreement_id ASC, t.version DESC, t.updated_at DESC";
            $rows = $stmt->FetchList($query);
            if (!$rows) {
                continue;
            }

            foreach ($rows as $keyRows => &$row) {
                $rows[$keyRows]["group_title_translation"] = GetTranslation(
                    "product-group-" . $row["group_code"],
                    "product"
                );

                if (!isset($row["version"])) {
                    continue;
                }

                $rows[$keyRows]["agreement_pdf_link"] = ADMIN_PATH . "module.php?load=" . $this->module .
                    "&Section=Employees&OrganizationID=" . $organizationID .
                    "&Employee=" . intval($user["employee_id"]) .
                    "&PdfAgreementId=" . intval($row["agreement_id"]);
            }

            $list[$key]["ServicesList"] = $rows;
            $list[$key]["ServicesCount"] = count($rows);
        }

        $this->_items = $list;
    }


    /**
     * Get agreements list for generate stored data
     *
     * @param string $dateFrom beginning of period stored data
     * @param string $dateTo end of period stored data
     * @param array $employees employees IDs
     *
     * @return array agreements list
     */
    public static function GetAgreementsListForStoredData($dateFrom, $dateTo, $employees)
    {
        $where = [];
        $where[] = "DATE(updated_at) >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "DATE(updated_at) <= " . Connection::GetSQLDate($dateTo);
        $where[] = "employee_id IN(" . implode(",", $employees) . ")";

        $stmt = GetStatement();
        $query = "SELECT employee_id, file FROM agreements_employee WHERE " . implode(" AND ", $where);

        return $stmt->FetchList($query);
    }
}
