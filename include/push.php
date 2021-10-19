<?php

class Push extends LocalObject
{
    /**
     * Constructor
     *
     * @param array $data Array of user properties to be loaded instantly
     */
    function Push($data = array())
    {
        parent::LocalObject($data);
    }

    /**
     * Creates operation from logging admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: date, user_id, ip, link, section, code
     *
     * @return bool true if operation is created successfully or false on failure
     */
    static function Save($userID, $isSended, $text, $deviceID, $errorMessage)
    {
        $isSended = $isSended ? "Y" : "N";

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO push_history (user_id, is_sended, text, created, device_id, error_message) VALUES (				
                    " . intval($userID) . ",
					" . Connection::GetSQLString($isSended) . ",
					" . Connection::GetSQLString($text) . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . Connection::GetSQLString($deviceID) . ",
                    " . Connection::GetSQLString($errorMessage) . ")
				RETURNING push_id";

        $stmt->Execute($query);
    }
}
