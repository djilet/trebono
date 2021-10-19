<?php

class PartnerContractList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function PartnerContractList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;

        $this->SetSortOrderFields(array(
            "start_date_asc" => "start_date ASC, created ASC",
            "start_date_desc" => "start_date DESC, created DESC"
        ));
        $this->SetOrderBy("start_date_asc");
    }

    /**
     * Loads contract list of partner for selected product
     *
     * @param int $partnerID partner_id of contracts partner
     * @param int $productID product which contracts should be loaded
     */
    public function LoadContractListByProductID($partnerID, $productID, $archive = false)
    {
        $where = array();
        $where[] = "p.partner_id=" . intval($partnerID);
        $where[] = "p.product_id=" . intval($productID);

        $where[] = $archive
            ? "end_date<" . Connection::GetSQLString(date('Y-m-d'))
            : "(end_date>='" . date('Y-m-d') . "' OR end_date IS NULL)";

        $query = "SELECT p.partner_contract_id, p.product_id, p.created, p.start_date, p.end_date, p.start_user_id, p.end_user_id, p.company_unit_id,
                      COALESCE(p.commission, '0') AS commission, COALESCE(p.implementation_fee, '0') AS implementation_fee, COALESCE(p.long, '0') AS long,
                      p.partner_type
					FROM partner_contract p				
					WHERE " . implode(" AND ", $where);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();
    }

    /**
     * Loads contract list of partner for selected company unit
     *
     * @param int $partnerID partner_id of contracts partner
     * @param int $companyUnitID company units which contracts should be loaded
     */
    public function LoadContractListByCompany($partnerID, $companyUnitID, $archive = false)
    {
        $where = array();
        $where[] = "p.partner_id=" . intval($partnerID);
        $where[] = "p.company_unit_id=" . intval($companyUnitID);

        $where[] = $archive
            ? "end_date<" . Connection::GetSQLString(date('Y-m-d'))
            : "(end_date>='" . date('Y-m-d') . "' OR end_date IS NULL)";

        $query = "SELECT p.partner_contract_id, p.product_id, p.created, p.start_date, p.end_date, p.start_user_id, p.end_user_id, p.company_unit_id,
                      COALESCE(p.commission, '0') AS commission, COALESCE(p.implementation_fee, '0') AS implementation_fee, COALESCE(p.long, '0') AS long,
                      p.partner_type
					FROM partner_contract p 				  
					WHERE " . implode(" AND ", $where);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $stmtMain = GetStatement(DB_MAIN);
        $partnerTypeList = new PartnerTypeList();
        $partnerTypeList->LoadPartnerTypeList();
        $partnerTypeList = $partnerTypeList->GetItems();
        $partnerTypeList = array_combine(array_column($partnerTypeList, "partner_type_id"), $partnerTypeList);

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $startUserInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($this->_items[$i]["start_user_id"]));
            if ($startUserInfo) {
                $this->_items[$i]["start_first_name"] = $startUserInfo["first_name"];
                $this->_items[$i]["start_last_name"] = $startUserInfo["last_name"];
            }

            $endUserInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($this->_items[$i]["end_user_id"]));
            if ($endUserInfo) {
                $this->_items[$i]["end_first_name"] = $endUserInfo["first_name"];
                $this->_items[$i]["end_last_name"] = $endUserInfo["last_name"];
            }

            $this->_items[$i]["company_unit"] = $stmtMain->FetchField("SELECT " . Connection::GetSQLDecryption("title") . " AS title FROM company_unit WHERE company_unit_id=" . intval($this->_items[$i]["company_unit_id"]));
            if (isset($partnerTypeList[$this->_items[$i]['partner_type']])) {
                $this->_items[$i]['partner_type'] = $partnerTypeList[$this->_items[$i]['partner_type']]["abbreviation"];
            }

            if ($this->_items[$i]["long"]) {
                continue;
            }

            $this->_items[$i]["long"] = "&infin;";
        }
    }
}
