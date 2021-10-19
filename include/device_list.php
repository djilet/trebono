<?php

class DeviceList extends LocalObjectList
{
    /**
     * Loads device list currently binded to passed user_id
     *
     * @param int $userID
     */
    public function LoadDeviceListByUserID($userID)
    {
        $query = "SELECT device_id, created, client, private_key, push_token, user_id, owner_id 
					FROM device 
					WHERE user_id=" . intval($userID);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
    }

    public function LoadDeviceListByVersion($userID, $versionList)
    {
        $where = array();
        $join = array();

        if (is_array($versionList) && count($versionList) > 0) {
            $join = " LEFT JOIN device_version AS dv ON dv.device_id = d.device_id";
            foreach ($versionList as $version) {
                $where[] = "(dv.version=" . Connection::GetSQLString($version['version']) . " AND d.client=" . Connection::GetSQLString($version['client']) . ")";
            }
        }

        $query = "SELECT d.device_id, d.created, d.client, d.private_key, d.push_token, d.user_id, d.owner_id, dv.version 
					FROM device AS d
					" . $join . "
					WHERE d.user_id=" . $userID . " " . (count($where) > 0 ? "AND (" . implode(
                        " OR ",
                        $where
                    ) . ")" : "") . " GROUP BY d.device_id, dv.version, d.client";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
    }

    public static function GetExtendedDeviceListByVersion($versionList, $versionOperation)
    {
        $where = array();
        $stmt = GetStatement(DB_PERSONAL);

        if (is_array($versionList) && count($versionList) > 0) {
            foreach ($versionList as $version) {
                $version = explode("-", $version);
                switch ($versionOperation) {
                    case "<":
                        $where[] = "(dv.version < " . Connection::GetSQLString($version[1]) . " AND d.client=" . Connection::GetSQLString($version[0]) . ")";
                        break;
                    case "<=":
                        $where[] = "(dv.version <= " . Connection::GetSQLString($version[1]) . " AND d.client=" . Connection::GetSQLString($version[0]) . ")";
                        break;
                    case ">":
                        $where[] = "(dv.version > " . Connection::GetSQLString($version[1]) . " AND d.client=" . Connection::GetSQLString($version[0]) . ")";
                        break;
                    case ">=":
                        $where[] = "(dv.version >= " . Connection::GetSQLString($version[1]) . " AND d.client=" . Connection::GetSQLString($version[0]) . ")";
                        break;
                    default:
                        $where[] = "(dv.version = " . Connection::GetSQLString($version[1]) . " AND d.client=" . Connection::GetSQLString($version[0]) . ")";
                        break;
                }
            }
        }

        $query = "SELECT concat(d.client, '-', dv.version) AS version
					FROM device AS d
					LEFT JOIN device_version AS dv ON dv.device_id = d.device_id
					" . (count($where) > 0 ? "WHERE (" . implode(
            " OR ",
            $where
        ) . ")" : "") . " GROUP BY d.device_id, dv.version, d.client";

        $result = array_keys($stmt->FetchIndexedList($query));
        $result = array_unique($result);

        return $result;
    }
}
