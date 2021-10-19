<?php

/**
 * User: der
 * Date: 13.09.18
 * Time: 12:46
 */

class PartnerContract extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contract properties to be loaded instantly
     */
    public function PartnerContract($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }


    /*
     * Creates new PartnerContract by given data
     * @param LocalObject $request - input data of new contract
     * */
    public function Create(LocalObject $request)
    {

        $required = array("product_id", "partner_id", "company_unit_id", "partner_type");
        foreach ($required as $param) {
            if (!$request->GetProperty($param)) {
                $this->AddError($param . "-required", $this->module);

                return false;
            }
        }

        $partnerType = new PartnerType();
        $partnerType->GetTypeByAbbr($request->GetProperty("partner_type"));
        $this->LoadFromObject($request);
        $this->SetProperty(
            "start_date",
            $startDate = $request->GetProperty("start_date") ? FormatDate(
                "Y-m-d",
                $request->GetProperty("start_date")
            ) :
                GetCurrentDateTime()
        );
        $this->SetProperty(
            "end_date",
            $endDate = $request->GetProperty("end_date") ? FormatDate("Y-m-d", $request->GetProperty("end_date")) : null
        );

        $commission = $request->IsPropertySet("commission")
            ? $request->GetPropertyForSQL("commission")
            :
            $partnerType->GetPropertyForSQL("commission");
        $implementation_fee = $request->IsPropertySet("implementation_fee")
            ? $request->GetPropertyForSQL("implementation_fee")
            :
            $partnerType->GetPropertyForSQL("implementation_fee");
        $long = $request->IsPropertySet("long")
            ? $request->GetPropertyForSQL("long")
            :
            $partnerType->GetPropertyForSQL("long");

        $stmt = GetStatement(DB_CONTROL);

        $query = "INSERT INTO partner_contract (partner_id, commission, implementation_fee, long, partner_type,
                      company_unit_id, product_id, created, start_date, end_date, start_user_id)
                    VALUES(" . $request->GetPropertyForSQL("partner_id") . ",
                        " . $commission . ", 
                        " . $implementation_fee . ", 
                        " . $long . ",
                        " . $partnerType->GetPropertyForSQL("partner_type_id") . ", 
                        " . $request->GetPropertyForSQL("company_unit_id") . ", 
                        " . $request->GetPropertyForSQL("product_id") . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . Connection::GetSQLDate($startDate) . ",
                        " . Connection::GetSQLString($endDate) . ",
                        " . $GLOBALS['user']->GetPropertyForSQL('user_id') . ")
                    RETURNING partner_contract_id";
        if (!$stmt->Execute($query)) {
            $this->AddError("cannot-create-contract", $this->module);

            return false;
        }
        $this->SetProperty("partner_contract_id", $stmt->GetLastInsertID());

        return true;
    }

    /*
     * Load PartnerContract by partner contract id
     * */
    public function LoadByContractID($contractID)
    {
        $query = "SELECT p.partner_contract_id, p.product_id, p.created, p.start_date, p.end_date, p.start_user_id, p.end_user_id, p.company_unit_id,
                      COALESCE(p.commission, '0') AS commission, COALESCE(p.implementation_fee, '0') AS implementation_fee, COALESCE(p.long, '0') AS long,
                      p.partner_type
					FROM partner_contract p 				  
					WHERE p.partner_contract_id=" . intval($contractID);

        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
    }

    /*
     * Set the end date of contract by specified date
     * If input date is null set current date as $end_date
     * @param string|null $end_date
     * */
    public function CloseContract($end_date = null)
    {
        $end_date = !$end_date ? Connection::GetSQLDate(date("Y-m-d")) : Connection::GetSQLDate($end_date);

        $user = $GLOBALS['user'];
        $user_id = $user->GetProperty("user_id");

        $stmt = GetStatement(DB_CONTROL);
        $query = "UPDATE partner_contract SET end_date=" . $end_date . ", end_user_id=" . intval($user_id) . "
            WHERE partner_contract_id=" . $this->GetPropertyForSQL("partner_contract_id");
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");
        } else {
            $this->AddMessage("contract-closed", "partner");
        }
    }

    /*
     * Check if the contract can or cannot be created.
     * @return boolean true if can or false if cannot.
     * */
    public function Validate()
    {

        $stmt = GetStatement(DB_CONTROL);

        $this->SetProperty(
            "start_date",
            $this->GetProperty("start_date") ? FormatDate("Y-m-d", $this->GetProperty("start_date")) :
                GetCurrentDateTime()
        );
        $this->SetProperty(
            "end_date",
            $this->GetProperty("end_date") ? FormatDate("Y-m-d", $this->GetProperty("end_date")) : null
        );

        $this_start = $this->GetPropertyForSQL("start_date");
        $this_end = $this->GetPropertyForSQL("end_date");
        $query = "SELECT partner_id FROM partner_contract WHERE company_unit_id=" . $this->GetIntProperty("company_unit_id") . "
                        AND product_id=" . $this->GetIntProperty("product_id") . "
                        AND (" . $this_start . "<=end_date OR end_date IS NULL) AND (start_date<=" . $this_end . " OR " . $this_end . " IS NULL)";

        return $stmt->FetchField($query);
    }
}
