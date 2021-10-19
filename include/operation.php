<?php

class Operation extends LocalObject
{
    /**
     * Constructor
     *
     * @param array $data Array of user properties to be loaded instantly
     */
    function __construct($data = array())
    {
        parent::LocalObject($data);
    }

    /**
     * Creates operation from logging admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: date, user_id, ip, link, section, code
     *
     * @return bool true if operation is created successfully or false on failure
     */
    static function Save($link, $section, $code, $object_id = null)
    {
        $stmt = GetStatement(DB_CONTROL);
        $user = new User();
        $user->LoadBySession();
        $query = "INSERT INTO operation (date, user_id, ip, link, section, code, object_id) VALUES (
                    " . Connection::GetSQLString(date_format(date_create(), 'Y-m-d H:i:s.v')) . ",					
                    " . $user->GetIntProperty("user_id") . ",
					" . $user->GetPropertyForSQL("last_ip") . ",
					" . Connection::GetSQLString($link) . ",
					" . Connection::GetSQLString($section) . ",
					" . Connection::GetSQLString($code) . ",
                    " . Connection::GetSQLString($object_id) . ")
				RETURNING operation_id";

        $stmt->Execute($query);
    }

    /**
     * Creates operation for logging cron activity
     *
     * @param $operationID int operation_id
     * @param $description string details about batch job, on what stage is it right now
     * @param $type string type of operation for filtering
     * @param null $error string list of errors
     * @param $usedIDs
     * @param bool $lastCall boolean determines whenever cron job is finished or not
     *
     * @return null or int - can return operation_id if it's the first call on current batch job
     */
    static function SaveCron($operationID, $description, $type, $error = null, $usedIDs = null, $lastCall = false)
    {
        if (!$lastCall) {
            $status = "N";
            $description .= "Batch job not finished</br>";
        } else {
            if (strlen($error) == 0) {
                $status = "Y";
                $description .= "Batch job finished successfully</br>";
            } else {
                $status = "N";
                $description .= "Errors occurred during batch job</br>";
            }
        }

        $stmt = GetStatement(DB_CONTROL);
        $user = new User();
        $user->LoadBySession();

        if ($operationID == null) {
            $query = "INSERT INTO operation_cron (date, description, type, is_successful, error_message) VALUES (
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",					
                    " . Connection::GetSQLString($description) . ",
                    " . Connection::GetSQLString($type) . ",
                    " . Connection::GetSQLString($status) . ",
                    " . Connection::GetSQLString($error) . ")
                    RETURNING operation_id";
        } else {
            $usedIDs = json_encode($usedIDs);
            $query = "UPDATE operation_cron 
					SET
					  description=" . Connection::GetSQLString($description) . ",
					  is_successful=" . Connection::GetSQLString($status) . ",
					  error_message=" . Connection::GetSQLString($error) . ",
					  used_ids=" . Connection::GetSQLString($usedIDs) . "
					WHERE operation_id=" . $operationID;
        }
        $stmt->Execute($query);

        if ($operationID == null) {
            return $stmt->GetLastInsertID();
        }
    }

    /**
     * Updates operation status for logging cron activity
     *
     * @param $operationID int operation_id
     * @param $status string details about batch job, on what stage is it right now
     */
    static function SaveCronStatus($operationID, $status)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "UPDATE operation_cron
				SET
				  status=" . Connection::GetSQLString($status) . ",
                  status_updated=" . Connection::GetSQLString(GetCurrentDateTime()) . "
				WHERE operation_id=" . $operationID;
        $stmt->Execute($query);
    }
}
