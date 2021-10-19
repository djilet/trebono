<?php

class Device extends LocalObject
{
    function __construct($data = array())
    {
        parent::LocalObject($data);
    }

    public function Register($deviceID, $client)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $privateKey = $this->GeneratePrivateKey($deviceID);
        $query = "SELECT COUNT(device_id) FROM device WHERE device_id=" . Connection::GetSQLString($deviceID);
        $query = $stmt->FetchField($query) == 0 ? "INSERT INTO device(device_id, private_key, client, created) 
				VALUES (" . Connection::GetSQLString($deviceID) . ", " . Connection::GetSQLString($privateKey) . ", " . Connection::GetSQLString($client) . ", " . Connection::GetSQLString(GetCurrentDateTime()) . ")" : "UPDATE device SET 
				private_key=" . Connection::GetSQLString($privateKey) . ", 
				client=" . Connection::GetSQLString($client) . ",
				user_id=NULL
				WHERE device_id=" . Connection::GetSQLString($deviceID);

        return $stmt->Execute($query) ? $privateKey : false;
    }

    public function IsDeviceRegistered($deviceID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT COUNT(*) FROM device WHERE device_id=" . Connection::GetSQLString($deviceID);

        return $deviceID && $stmt->FetchField($query) == 1;
    }

    public function SetPushToken($deviceID, $token)
    {
        if (strlen($token) > 0) {
            $stmt = GetStatement(DB_PERSONAL);
            $query = "UPDATE device SET	push_token=" . Connection::GetSQLString($token) . " WHERE device_id=" . Connection::GetSQLString($deviceID);
            if ($stmt->Execute($query)) {
                return true;
            }
        }

        return false;
    }

    public function GetUserID($deviceID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id FROM device WHERE device_id=" . Connection::GetSQLString($deviceID);

        return $stmt->FetchField($query);
    }

    public function SetUserID($deviceID, $userID)
    {
        if ($userID > 0) {
            $this->SetOwnerID($deviceID, $userID);

            $stmt = GetStatement(DB_PERSONAL);

            $query = "UPDATE device SET
			user_id=" . intval($userID) . "
			WHERE device_id=" . Connection::GetSQLString($deviceID);

            if ($stmt->Execute($query)) {
                return true;
            }
        }

        return false;
    }

    public function SetOwnerID($deviceID, $userID)
    {
        $stmt = GetStatement(DB_PERSONAL);

        $query = "UPDATE device SET
		owner_id=" . intval($userID) . "
		WHERE device_id=" . Connection::GetSQLString($deviceID) . " AND owner_id IS NULL";

        $stmt->Execute($query);
    }

    private function GeneratePrivateKey($deviceID)
    {
        return md5($deviceID . date("U") . rand(1000, 9999));
    }

    public function GetPrivateKey($deviceID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT private_key FROM device WHERE device_id=" . Connection::GetSQLString($deviceID);

        return $stmt->FetchField($query);
    }

    public static function GetDeviceAmountByUserID($userID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT COUNT(device_id) FROM device WHERE user_id=" . Connection::GetSQLString($userID);

        return $stmt->FetchField($query);
    }

    public static function GetClientByDeviceID($deviceID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT client FROM device WHERE device_id=" . Connection::GetSQLString($deviceID);

        return $stmt->FetchField($query);
    }

    /**
     * Inserts currunt version of device if != last
     */
    public function SaveVersion($deviceID, $userID, $version)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT dv.version
                    FROM device_version AS dv
                    WHERE dv.device_id=" . Connection::GetSQLString($deviceID) . "
                        AND dv.user_id=" . intval($userID) . "
                    ORDER BY dv.created DESC";

        if ($stmt->FetchField($query) != $version) {
            $query = "INSERT INTO device_version(device_id, version, user_id, created)
				VALUES (" . Connection::GetSQLString($deviceID) . ", " . Connection::GetSQLString($version) . ", " . intval($userID) . ", " . Connection::GetSQLString(GetCurrentDateTime()) . ")";

            return $stmt->Execute($query);
        }

        return true;
    }

    /**
     * Gets history of version for device_id
     */
    public static function GetDeviceVersionListByDeviceID($deviceID, $userID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT dv.device_id, dv.version, d.client, dv.created, dv.user_id
                    FROM device_version AS dv
                        LEFT JOIN device AS d ON dv.device_id=d.device_id
                    WHERE dv.user_id=" . intval($userID) . "
                        AND dv.device_id=" . Connection::GetSQLString($deviceID) . "
                    ORDER BY dv.created DESC";

        return $stmt->FetchList($query);
    }

    /**
     * Gets last version_id of device
     */
    public static function GetLastVersionIDByDeviceID($deviceID, $userID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT DISTINCT ON(dv.device_id) dv.device_id, dv.version, dv.created, dv.user_id, dv.version_id
                        FROM device_version AS dv
                        WHERE dv.user_id=" . intval($userID) . "
                            AND dv.device_id=" . Connection::GetSQLString($deviceID) . "
                        ORDER BY dv.device_id, dv.created DESC";
        $row = $stmt->FetchRow($query);

        return $row ? $row["version_id"] : false;
    }

    /**
     * Gets last version_id of device before date
     */
    public static function GetLastVersionByDeviceIDBeforeDate($deviceID, $userID, $date)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT d.device_id, dv.version, dv.version_created, dv.version_id, d.client
                    FROM device AS d 
                    LEFT JOIN (SELECT DISTINCT ON(dv.device_id) dv.device_id, dv.version, dv.created AS version_created, dv.user_id, dv.version_id 
                                FROM device_version AS dv 
                                WHERE dv.device_id=" . Connection::GetSQLString($deviceID) . "
                                    AND dv.user_id=" . intval($userID) . "
                                    AND dv.created<" . Connection::GetSQLDateTime($date) . "
                                ORDER BY dv.device_id, dv.created DESC) 
                        AS dv ON dv.device_id=d.device_id
                    WHERE d.device_id=" . Connection::GetSQLString($deviceID);

        return $stmt->FetchRow($query);
    }

    /**
     * Gets device by version_id
     */
    public static function GetDeviceByVersionID($versionID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT dv.version_id, dv.device_id, d.client, dv.version, d.owner_id
                        FROM device_version AS dv
                            LEFT JOIN device AS d ON dv.device_id=d.device_id
                        WHERE dv.version_id=" . intval($versionID);

        return $stmt->FetchRow($query);
    }

    /**
     * Gets device list with last version for user_id
     */
    public static function GetDeviceVersionList($userID)
    {
        $stmt = GetStatement(DB_PERSONAL);

        $query = "SELECT DISTINCT ON(dv.device_id) dv.device_id, dv.version, d.client, dv.created, dv.user_id, dv.version_id
                        FROM device_version AS dv
                            LEFT JOIN device AS d ON dv.device_id=d.device_id
                        WHERE dv.user_id=" . intval($userID) . "
                        ORDER BY dv.device_id, dv.created DESC";

        return $stmt->FetchList($query);
    }
}
