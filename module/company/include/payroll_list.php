<?php

class PayrollList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function PayrollList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "date_asc" => "payroll_month ASC, created ASC",
            "date_desc" => "payroll_month DESC, created DESC"
        ));
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads payroll list for company view
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadPayrollList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPagePayroll")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPagePayroll"));
        }

        $where = array();

        //Permission filter
        $user = new User();
        $user->LoadBySession();
        $permissionList = $user->GetProperty("PermissionList");
        $permissionLinks = array();
        foreach ($permissionList as $permission) {
            if ($permission['name'] == "root") {
                $permissionLinks = array();

                if ($request->IsPropertySet("DatePayroll")) {
                    $where[] = "p.payroll_month='" . date_create($request->GetProperty("DatePayroll"))->modify("-1 month")->format("Ym") . "'";
                }
                if ($request->GetProperty("FilterCreatedRange")) {
                    [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
                    $where[] = "p.created >= " . Connection::GetSQLDateTime($from);
                    $where[] = "p.created <= " . Connection::GetSQLDateTime($to);
                }
                if ($request->GetProperty("FilterTitle")) {
                    $where[] = Connection::GetSQLDecryption("c.title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
                }

                break;
            }
            if ($permission['name'] != "payroll" && $permission['name'] != "tax_auditor" && $permission['name'] != "bookkeeping_export") {
                continue;
            }

            $permissionLinks[] = $permission['link_id'];
        }
        $permissionLinks = array_filter($permissionLinks);

        if ($permissionLinks) {
            $where[] = "p.company_unit_id IN (" . implode(",", $permissionLinks) . ")";
        }

        $query = "SELECT p.payroll_id, p.company_unit_id, p.payroll_month, p.created, p.pdf_file, p.lodas_file, p.lug_file, p.logga_file, p.topas_file, p.addison_file, p.lexware_file, p.perforce_file, p.sage_file, p.status,
                      " . Connection::GetSQLDecryption("c.title") . " AS title
                  	FROM payroll p 
						LEFT JOIN company_unit c ON p.company_unit_id=c.company_unit_id
                  	" . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "");

        if ($request->IsPropertySet("PagePayroll")) {
            $this->SetCurrentPage($request->GetProperty("PagePayroll"));
        } else {
            $this->SetCurrentPage();
        }

        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Loads payroll list for web api
     *
     * @param LocalObject $request object of parameters data
     */
    public function LoadPayrollListForApi($request)
    {
        $this->SetItemsOnPage(0);

        $where = array();
        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "p.created >= " . Connection::GetSQLDateTime($from);
            $where[] = "p.created <= " . Connection::GetSQLDateTime($to);
        }

        if ($request->GetProperty("company_unit_id")) {
            $where[] = "p.company_unit_id = " . $request->GetProperty("company_unit_id");
        }

        $query = "SELECT p.payroll_id, p.company_unit_id, p.payroll_month, p.created, p.pdf_file, p.lodas_file, p.lug_file, p.logga_file, p.topas_file, p.status,
                      " . Connection::GetSQLDecryption("c.title") . " AS title
                  	FROM payroll p 
						LEFT JOIN company_unit c ON p.company_unit_id=c.company_unit_id
                  	" . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow(true);
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     *
     * @param bool $for_api changes ammount of fields
     */
    private function PrepareContentBeforeShow($for_api = false)
    {
        $datevLugIniList = Config::GetConfigHistory("export_datev_lug_ini");

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $period = date_create_from_format("!Ym", $this->_items[$i]['payroll_month']);
            if (!$period) {
                $period = date_create("1970-01-01");
            }
            $this->_items[$i]['Period'] = GetTranslation("date-" . $period->format("F")) . " " . $period->format("Y");

            if ($for_api) {
                continue;
            }

            if ($this->_items[$i]["status"]) {
                $this->_items[$i]["status_title"] = GetTranslation(
                    "invoice-status-" . $this->_items[$i]["status"],
                    $this->module
                );
            }

            foreach ($datevLugIniList as $datevLugIni) {
                if (strtotime($datevLugIni["created"]) >= strtotime($this->_items[$i]["created"])) {
                    continue;
                }

                $this->_items[$i]["lug_ini_file"] = $datevLugIni["value"];
            }
            PrepareDownloadPath($this->_items[$i], "lug_ini_file", CONFIG_FILE_DIR, CONTAINER__CORE);
        }
    }

    /**
     * Get payroll list for the period
     *
     * @param string $dateFrom beginning of period stored data
     * @param string $dateTo end of period stored data
     * @param string $companyUnitID id company unit
     *
     * @return array receipt IDs
     */
    public static function GetPayrollListForStoredData($dateFrom, $dateTo, $companyUnitID)
    {
        $monthBegin = intval(date("n", strtotime($dateFrom)));
        $monthEnd = intval(date("n", strtotime($dateTo)));
        $year = date("Y", strtotime($dateTo));

        $months = array();
        for ($i = $monthBegin; $i <= $monthEnd; $i++) {
            $months[] = $i < 10 ? $year . '0' . $i : $year . $i;
        }

        $where = array();
        $where[] = "payroll_month IN ('" . implode("', '", $months) . "')";
        $where[] = "company_unit_id = " . $companyUnitID;

        $stmt = GetStatement();
        $query = "SELECT payroll_id FROM payroll WHERE " . implode(" AND ", $where);

        $payrollList = $stmt->FetchList($query);

        return array_column($payrollList, "payroll_id");
    }
}
