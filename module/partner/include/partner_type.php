<?php

/**
 * User: der
 * Date: 14.09.18
 * Time: 18:33
 */

class PartnerType extends LocalObject
{
    /**
     * Constructor
     *
     * @param array $data Array of partner_type properties to be loaded instantly
     */
    public function PartnerType($data = array())
    {
        parent::LocalObject($data);
    }

    /**
     * Returns partner_type information by its unique abbreviation
     *
     * @param string $abbr type's abbreviation
     */
    function GetTypeByAbbr($abbr)
    {
        if (!$abbr) {
            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT * FROM partner_type WHERE abbreviation=" . Connection::GetSQLString($abbr);
        $this->LoadFromSQL($query, $stmt);
    }

    /**
     * Returns partner_type information by its ID
     *
     * @param int $id type's ID
     */
    function GetTypeByID($id)
    {
        if (!$id) {
            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT * FROM partner_type WHERE partner_type_id=" . intval($id);
        $this->LoadFromSQL($query, $stmt);
    }

    /*
     * Updates existing Partner type.
     * Every param can be overwritten by empty/zero value, so be careful using it.
     * Cannot create new Partner types
     * @return boolean true on success or false on failure
     */
    function Save()
    {
        if (!in_array($this->GetProperty("partner_type"), ["BP", "KB", "LP"])) {
            $this->AddError("config-no_such_partnertype");

            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE partner_type SET 
                    commission=" . $this->GetPropertyForSQL("commission") . ",
                    implementation_fee=" . $this->GetPropertyForSQL("implementation_fee") . ",
                    long=" . $this->GetPropertyForSQL("long") . "
                  WHERE abbreviation=" . $this->GetPropertyForSQL("partner_type");
        if ($stmt->Execute($query)) {
            $this->AddMessage("config-partnertype_updated");

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }
}
