<?php

class Currency extends LocalObject
{
    /**
     * Constructor
     *
     * @param array $data Array of user properties to be loaded instantly
     */
    function Currency($data = array())
    {
        parent::LocalObject($data);
    }

    /**
     * Returns 3-digit of currency by it's id
     *
     * @param int $currency_id
     *
     * @return string
     */
    static function GetDigitByID($currency_id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT digit FROM currency WHERE currency_id = " . Connection::GetSQLString($currency_id);

        return $stmt->FetchField($query);
    }

    static function GetSymbolByID($currency_id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT symbol FROM currency WHERE currency_id = ".Connection::GetSQLString($currency_id);
        return $stmt->FetchField($query);
    }

    static function GetDefaultID()
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT currency_id FROM currency WHERE digit = 'EUR'";

        return $stmt->FetchField($query);
    }
}
