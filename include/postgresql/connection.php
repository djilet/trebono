<?php

require_once(dirname(__FILE__) . '/statement.php');

class Connection
{
    static $_dbLink;
    static $_checkTime;
    static $_encryptionKey = "HSAZFd3exl6Rcj5xmks2XfJB4glvbX9O";

    function __construct($server = '', $databaseName = '', $userName = '', $password = '', $encoding = 'utf8')
    {
        //if local connect directly to db
        if (IsLocalEnvironment()) {
            static::$_dbLink = pg_connect("host=" . $server . " dbname=" . $databaseName . " user=" . $userName . " password=" . $password);
        } //on meshcloud try to connect to pg bouncer
        else {
            static::$_dbLink = @pg_connect("host=127.0.0.1 port=6432 dbname=" . $databaseName);

            //if not successfull try to connect directly to db (phinx and workers starts before pb bouncer start)
            if (!is_resource(static::$_dbLink)) {
                static::$_dbLink = pg_connect("host=" . $server . " dbname=" . $databaseName . " user=" . $userName . " password=" . $password);
            }
        }
        static::$_checkTime = time();
        if (!is_resource(static::$_dbLink)) {
            ErrorHandler::TriggerError('Can\'t connect to database server.', E_ERROR);
        }
        pg_query(static::$_dbLink, "SET NAMES " . $this->GetSQLString($encoding));
    }

    function &CreateStatement($resultType = PGSQL_ASSOC, $errorLevel = E_USER_WARNING)
    {
        $stmt = new Statement(static::$_dbLink, $resultType, $errorLevel);
        return $stmt;
    }

    static function GetSQLString($str)
    {
        if (is_null($str)) {
            return "NULL";
        }
        return "'" . pg_escape_string(static::$_dbLink, $str) . "'";
    }

    static function GetSQLLike($str)
    {
        return addcslashes(pg_escape_string(static::$_dbLink, $str), "\\_%'");
    }

    static function GetSQLArray($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $arr[$key] = Connection::GetSQLString($value);
            }
        }
        return $arr;
    }

    static function GetSQLDate($str)
    {
        $str = str_replace(",", " ", $str);
        if (empty($str)) {
            return self::GetSQLString(null);
        } else {
            return self::GetSQLString(date("Y-m-d", strtotime($str)));
        }
    }

    static function GetSQLDateTime($str)
    {
        $str = str_replace(",", " ", $str);
        if (empty($str)) {
            return self::GetSQLString(null);
        } else {
            return self::GetSQLString(date("Y-m-d H:i:s", strtotime($str)));
        }
    }

    static function GetSQLSearchRegexp($str)
    {
        if (empty($str)) {
            return self::GetSQLString('');
        }

        $map = array(
            "ü" => "ue",
            "ö" => "oe",
            "ä" => "ae",
            "ß" => "ss"
        );
        $map = array_merge($map, array_flip($map));

        $callback = function ($v) use ($map) {
            return "(" . $v[0] . "|" . $map[$v[0]] . ")";
        };

        $str = preg_quote($str);
        $regexp = preg_replace_callback("/" . implode("|", array_keys($map)) . "/Uu", $callback, $str);
        return self::GetSQLString(".*" . $regexp . ".*");
    }

    static function GetSQLEncryption($column)
    {
        return "encrypt(convert_to(" . $column . ", 'utf-8')::bytea, " . self::GetSQLString(self::$_encryptionKey) . "::bytea, 'bf')";
    }

    static function GetSQLDecryption($column)
    {
        return "convert_from(decrypt(" . $column . "::bytea, " . self::GetSQLString(self::$_encryptionKey) . "::bytea, 'bf')::bytea, 'utf-8')";
    }

    function ReconnectIfNeeded()
    {
        if (time() - static::$_checkTime >= 90) {
            pg_ping(static::$_dbLink);
            static::$_checkTime = time();
        }
    }
}

class ConnectionPersonal extends Connection
{
    static $_dbLink;
    static $_checkTime;
}

class ConnectionControl extends Connection
{
    static $_dbLink;
    static $_checkTime;
}

?>