<?php

class Email extends LocalObject
{
    /**
     * Constructor
     *
     * @param array $data Array of user properties to be loaded instantly
     */
    function Email($data = array())
    {
        parent::LocalObject($data);
    }

    /**
     * Creates operation from logging admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: date, user_id, ip, link, section, code
     *
     * @return bool true if operation is created successfully or false on failure
     */
    static function Save($userID, $email, $isSended, $title, $fileName, $errorMessage)
    {
        $isSended = $isSended ? "Y" : "N";

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO email_history (user_id, email, is_sended, title, file_name, created, error_message) VALUES (				
                    " . intval($userID) . ",
                    " . Connection::GetSQLString($email) . ",
					" . Connection::GetSQLString($isSended) . ",
					" . Connection::GetSQLString($title) . ",
					" . Connection::GetSQLString($fileName) . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . Connection::GetSQLString($errorMessage) . ")
				RETURNING email_id";

        $stmt->Execute($query);
    }

    static function GetFileNameByID($emailID)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT file_name FROM email_history WHERE email_id = " . $emailID;

        return $stmt->FetchField($query);
    }
}
