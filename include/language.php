<?php

class Language extends Object
{

    var $_tags;
    var $_values;
    var $_dataLanguageCode;
    var $_dataLanguageList = null;
    var $_interfaceLanguageCode;
    var $_interfaceLanguageList = null;
    var $_translatePHP = null;
    var $_translateTemplate = null;
    var $_cacheLifeTime = 604800;
    var $_cacheFile;
    var $_mysqlCharsetMap;
    var $_dateTimeFormatMap;

    function Language()
    {
        $this->_mysqlCharsetMap = array(
            'big5' => 'big5',
            'cp-866' => 'cp866',
            'euc-jp' => 'ujis',
            'euc-kr' => 'euckr',
            'gb2312' => 'gb2312',
            'gbk' => 'gbk',
            'iso-8859-1' => 'latin1',
            'iso-8859-2' => 'latin2',
            'iso-8859-7' => 'greek',
            'iso-8859-8' => 'hebrew',
            'iso-8859-8-i' => 'hebrew',
            'iso-8859-9' => 'latin5',
            'iso-8859-13' => 'latin7',
            'iso-8859-15' => 'latin1',
            'koi8-r' => 'koi8r',
            'shift_jis' => 'sjis',
            'tis-620' => 'tis620',
            'utf-8' => 'utf8',
            'windows-1250' => 'cp1250',
            'windows-1251' => 'cp1251',
            'windows-1252' => 'latin1',
            'windows-1256' => 'cp1256',
            'windows-1257' => 'cp1257',
        );

        $this->_dateTimeFormatMap = array(
            '%' => '%%', // a literal % character
            'A' => '%p', // "PM" or "AM"
            'a' => '%P', // "pm" or "am"
            'D' => '%a', // abbreviated weekday name
            'l' => '%A', // full weekday name
            'M' => '%b', // abbreviated month name
            'F' => '%B', // full month name
            'd' => '%d', // the day of the month ( 01 .. 31 )
            'j' => '%e', // the day of the month ( 1 .. 31 )
            'H' => '%H', // hour ( 00 .. 23 )
            'h' => '%I', // hour ( 01 .. 12 )
            'z' => '%j', // day of the year ( 0 .. 365 )
            'G' => '%k', // hour ( 0 .. 23 )
            'g' => '%l', // hour ( 1 .. 12 )
            'm' => '%m', // month ( 01 .. 12 )
            'i' => '%M', // minute ( 00 .. 59 )
            's' => '%S', // second ( 00 .. 59 )
            'U' => '%s', // number of seconds since Epoch (since Jan 01 1970 00:00:00 UTC)
            'W' => '%W', // the week number (ISO 8601)
            'w' => '%w', // the day of the week ( 0 .. 6, 0 = SUN )
            'y' => '%y', // year without the century ( 00 .. 99 )
            'Y' => '%Y', // year including the century ( ex. 1979 )
            '\t' => '%t', // a tab character
            '\n' => '%n', // a new line character
            '\\' => '\\\\', // backslash
        );
    }

    function SetLanguageList($lngList, $setUTF8)
    {
        $this->_dataLanguageList = $lngList;
        if (isset($this->_dataLanguageList[DATA_LANGCODE])) {
            $this->_dataLanguageCode = DATA_LANGCODE;
            foreach ($this->_dataLanguageList as $k => $v) {
                if ($k == DATA_LANGCODE) {
                    $this->_dataLanguageList[$k]["Selected"] = 1;
                } else {
                    unset($this->_dataLanguageList[$k]["Selected"]);
                }
            }
        }
        if ($setUTF8) {
            foreach ($this->_dataLanguageList as $k => $v) {
                $this->_dataLanguageList[$k]["Encoding"] = "utf-8";
            }
        }

        $request = new LocalObject(array_merge($_GET, $_POST));
        $cookie = new LocalObject($_COOKIE);

        $iLangCode = "";
        $setILang = false;
        if (strlen($request->GetProperty("InterfaceLanguage")) > 0) {
            // Switch to another interface language (because data is submitted from form)
            $iLangCode = $request->GetProperty("InterfaceLanguage");
            $setILang = true;
        } else {
            if (strlen($cookie->GetProperty("ILangCode")) > 0) {
                // Use interface language from cookie
                $iLangCode = $cookie->GetProperty("ILangCode");
            }
        }

        $this->_interfaceLanguageList = array();
        if ($dh = opendir(PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/")) {
            $firstLang = null;
            while (($file = readdir($dh)) !== false) {
                if ($file == "." || $file == ".." || $file == ".svn") {
                    continue;
                }
                if (!is_dir(PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . $file)) {
                    continue;
                }

                if (is_null($firstLang)) {
                    $firstLang = $file;
                }
                if (isset($this->_dataLanguageList[$file])) {
                    $this->_interfaceLanguageList[$file] = array(
                        "Folder" => $file,
                        "Name" => $this->_dataLanguageList[$file]["Name"],
                        "NativeName" => $this->_dataLanguageList[$file]["NativeName"]
                    );
                } else {
                    $this->_interfaceLanguageList[$file] = array(
                        "Folder" => $file,
                        "Name" => $file,
                        "NativeName" => $file
                    );
                }
            }
            closedir($dh);
        }

        if (count($this->_interfaceLanguageList) > 0) {
            if (!isset($this->_interfaceLanguageList[$iLangCode])) {
                $setILang = true;
                $iLangCode = isset($this->_interfaceLanguageList[DATA_LANGCODE]) ? DATA_LANGCODE : $firstLang;
            }
            $this->_interfaceLanguageList[$iLangCode]["Selected"] = true;
            $this->_interfaceLanguageCode = $iLangCode;
            define("INTERFACE_LANGCODE", $iLangCode);

            if ($setILang) {
                setcookie("ILangCode", INTERFACE_LANGCODE, time() + 60 * 60 * 24 * 30 * COOKIE_EXPIRE, PROJECT_PATH);
            }
        } else {
            ErrorHandler::TriggerError("No language is defined in folder \"" . PROJECT_DIR . "language/\"!", E_ERROR);
        }
    }

    function GetDataLanguageByCode($lngCode)
    {
        return $this->_dataLanguageList[$lngCode] ?? array();
    }

    function GetDataLanguageList()
    {
        return $this->_dataLanguageList;
    }

    function GetDataLanguageName()
    {
        return $this->_dataLanguageList[$this->_dataLanguageCode]['NativeName'];
    }

    function GetInterfaceLanguageList()
    {
        return $this->_interfaceLanguageList;
    }

    function GetInterfaceLanguageName()
    {
        return $this->_interfaceLanguageList[$this->_interfaceLanguageCode]['NativeName'];
    }

    function GetHTMLCharset()
    {
        return defined('IS_ADMIN')
            ? "utf-8"
            : strtolower($this->_dataLanguageList[$this->_dataLanguageCode]["Encoding"]);
    }

    function GetMySQLEncoding()
    {
        if (defined('IS_ADMIN')) {
            return "utf8";
        }

        $currentEncoding = strtolower($this->_dataLanguageList[$this->_dataLanguageCode]["Encoding"]);
        if (isset($this->_mysqlCharsetMap[$currentEncoding])) {
            return $this->_mysqlCharsetMap[$currentEncoding];
        }

        ErrorHandler::TriggerError("Encoding \"" . $currentEncoding . "\" is not supported!", E_ERROR);
    }

    function _ConvertForPHP($format)
    {
        $format = str_replace('\t', "\t", $format);
        $format = str_replace('\n', "\n", $format);
        $format = str_replace('\\', '\\\\', $format);
        $uncompatibleSymbols = array('B', 'c', 'I', 'L', 'n', 'O', 'r', 'S', 't', 'T', 'Z');
        for ($i = 0; $i < count($uncompatibleSymbols); $i++) {
            $format = str_replace($uncompatibleSymbols[$i], '\\' . $uncompatibleSymbols[$i], $format);
        }

        return $format;
    }

    function GetDateFormat()
    {
        if (!isset($this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForPHP"])) {
            $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForPHP"] = $this->_ConvertForPHP($this->_dataLanguageList[$this->_dataLanguageCode]["DateFormat"]);
        }

        return $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForPHP"];
    }

    function GetTimeFormat()
    {
        if (!isset($this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForPHP"])) {
            $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForPHP"] = $this->_ConvertForPHP($this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormat"]);
        }

        return $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForPHP"];
    }

    function GetDateFormatForJS()
    {
        if (!isset($this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForJS"])) {
            $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForJS"] = $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormat"];
            foreach ($this->_dateTimeFormatMap as $phpCode => $jsCode) {
                $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForJS"] = str_replace(
                    $phpCode,
                    $jsCode,
                    $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForJS"]
                );
            }
        }

        return $this->_dataLanguageList[$this->_dataLanguageCode]["DateFormatForJS"];
    }

    function GetTimeFormatForJS()
    {
        if (!isset($this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForJS"])) {
            $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForJS"] = $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormat"];
            foreach ($this->_dateTimeFormatMap as $phpCode => $jsCode) {
                $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForJS"] = str_replace(
                    $phpCode,
                    $jsCode,
                    $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForJS"]
                );
            }
        }

        return $this->_dataLanguageList[$this->_dataLanguageCode]["TimeFormatForJS"];
    }

    function GetTranslation($key, $module = null, $replacements = array(), $forceLanguage = null)
    {
        $this->_LoadForPHP($module, $forceLanguage);

        $session = GetSession();

        if (
            $session->GetProperty("EditVariables") && isset($this->_translatePHP["module" . $module][$key]) && in_array(
                "editable",
                $this->_translatePHP["module" . $module][$key]["AttributeList"]
            )
        ) {
            $value = $this->_translatePHP["module" . $module][$key];

            return Language::ReplacePairs(
                $value["Value"],
                $replacements
            ) . "#variable_" . $value["AttributeList"]["variable_id"] . "#";
        }

        if (isset($this->_translatePHP["module" . $module][$key])) {
            return PrepareContentBeforeShow(Language::ReplacePairs(
                $this->_translatePHP["module" . $module][$key]["Value"],
                $replacements
            ));
        }

        // Translation is not found
        return $key;
    }

    function ReplacePairs($str = '', $replacements = array(), $open = '%', $close = '%')
    {
        if (strlen($str) > 0 && count($replacements) > 0) {
            $resReplace = array();
            foreach ($replacements as $key => $value) {
                $resReplace[$open . $key . $close] = $value;
            }
            $str = str_replace(array_keys($resReplace), array_values($resReplace), $str);
        }

        return $str;
    }

    function LoadForTempate($template, $module, $isAdmin, $includeCommon = true)
    {
        $lang = defined('IS_ADMIN') ? $this->_interfaceLanguageCode : $this->_dataLanguageCode;

        $template = str_replace("/", "_", $template);
        if (!isset($this->_translateTemplate["module" . $module])) {
            $data = array();
            $files = array();

            $files[] = PROJECT_DIR . "language/" . $lang . "/_template.xml";
            $files[] = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . $lang . "/_template.xml";
            if (strlen($module) > 0) {
                $files[] = PROJECT_DIR . "language/" . $lang . "/" . $module . "_template.xml";
                $files[] = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . $lang . "/" . $module . "_template.xml";
            }

            if ($this->_CheckCache($files)) {
                $data = unserialize(fread($fp = fopen($this->_cacheFile, 'r'), filesize($this->_cacheFile)));
            } else {
                $variablesFromDB = self::GetFromDB($lang, "template", "core");
                $variablesFromDBArray = array();
                foreach ($variablesFromDB as $variableFromDB) {
                    if ($variableFromDB["template"] == "common") {
                        $variablesFromDBArray["common"][$variableFromDB["tag_name"]] = array(
                            "Value" => $variableFromDB["value"],
                            "AttributeList" => array(
                                "editable" => true,
                                "tag_name" => $variableFromDB["tag_name"],
                                "variable_id" => $variableFromDB["variable_id"]
                            )
                        );
                    } else {
                        $variablesFromDBArray["admin"][$variableFromDB["template"]][$variableFromDB["tag_name"]] = array(
                            "Value" => $variableFromDB["value"],
                            "AttributeList" => array(
                                "editable" => true,
                                "tag_name" => $variableFromDB["tag_name"],
                                "variable_id" => $variableFromDB["variable_id"]
                            )
                        );
                    }
                }
                $data = array_merge_recursive2($data, $variablesFromDBArray);

                if (strlen($module) > 0) {
                    $variablesFromDB = self::GetFromDB($lang, "template", $module);
                    $variablesFromDBArray = array();
                    foreach ($variablesFromDB as $variableFromDB) {
                        if ($variableFromDB["template"] == "common") {
                            $variablesFromDBArray["common"][$variableFromDB["tag_name"]] = array(
                                "Value" => $variableFromDB["value"],
                                "AttributeList" => array(
                                    "editable" => true,
                                    "tag_name" => $variableFromDB["tag_name"],
                                    "variable_id" => $variableFromDB["variable_id"]
                                )
                            );
                        } else {
                            $variablesFromDBArray["admin"][$variableFromDB["template"]][$variableFromDB["tag_name"]] = array(
                                "Value" => $variableFromDB["value"],
                                "AttributeList" => array(
                                    "editable" => true,
                                    "tag_name" => $variableFromDB["tag_name"],
                                    "variable_id" => $variableFromDB["variable_id"]
                                )
                            );
                        }
                    }
                    $data = array_merge_recursive2($data, $variablesFromDBArray);
                }

                /*for ($i = 0; $i < count($files); $i++)
                {
                    if ($this->_LoadXML($files[$i]))
                        $data = array_merge_recursive2($data, $this->_GetTemplateXMLAsArray());
                }*/
                $this->_CreateCache(serialize($data));
            }

            $this->_translateTemplate["module" . $module] = $data;
            $this->_Convert($this->_translateTemplate["module" . $module]);
        }

        // Get commmon translation
        $result = isset($this->_translateTemplate["module" . $module]['common']) && $includeCommon
            ? $this->_translateTemplate["module" . $module]['common']
            : array();

        // Get actual translation for curent template and merge it with commmon translation
        if ($isAdmin && isset($this->_translateTemplate["module" . $module]['admin'][$template]) && is_array($this->_translateTemplate["module" . $module]['admin'][$template])) {
            $result = array_merge($result, $this->_translateTemplate["module" . $module]['admin'][$template]);
        } else {
            if (isset($this->_translateTemplate["module" . $module]['public'][$template]) && is_array($this->_translateTemplate["module" . $module]['public'][$template])) {
                $result = array_merge($result, $this->_translateTemplate["module" . $module]['public'][$template]);
            }
        }

        return $result;
    }

    static function GetFromDB(
        $languageCode,
        $type,
        $module = "core",
        $template = null,
        $tagName = null,
        $fullList = false
    ) {
        $stmt = GetStatement();

        $where = array();

        if (!$fullList) {
            if ($languageCode) {
                $where[] = "language_code=" . Connection::GetSQLString($languageCode);
            }
            $where[] = "type=" . Connection::GetSQLString($type);
            if ($template) {
                $where[] = "template=" . Connection::GetSQLString($template);
            }

            $where[] = "module=" . Connection::GetSQLString($module);

            if ($tagName) {
                $where[] = "tag_name=" . Connection::GetSQLString($tagName);
            }
        }

        $query = "SELECT * FROM language_variable " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "") . " ORDER BY tag_name";

        return $tagName && $languageCode ? $stmt->FetchRow($query) : $stmt->FetchList($query);
    }

    static function SaveToDB($id, $value)
    {
        $stmt = GetStatement();
        $query = "UPDATE language_variable SET value=" . Connection::GetSQLString($value) . " WHERE variable_id=" . intval($id);
        $stmt->Execute($query);
    }

    static function CreateInDB($langCode, $type, $module, $template, $tagName, $value)
    {
        $stmt = GetStatement();
        $query = "INSERT INTO language_variable (tag_name, value, type, module, template, language_code) VALUES (
                    " . Connection::GetSQLString($tagName) . ",
                    " . Connection::GetSQLString($value) . ",
                    " . Connection::GetSQLString($type) . ",
                    " . Connection::GetSQLString($module) . ",
                    " . Connection::GetSQLString($template) . ",
                    " . Connection::GetSQLString($langCode) . ")
                ON CONFLICT(tag_name, type, module, template, language_code) DO UPDATE 
                    SET value=" . Connection::GetSQLString($value) . ";";

        return $stmt->Execute($query) ? true : false;
    }

    static function GetByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT value FROM language_variable WHERE variable_id=" . intval($id);

        return $stmt->FetchField($query);
    }

    static function GetByID2($id)
    {
        $stmt = GetStatement();
        $query = "SELECT * FROM language_variable WHERE variable_id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    static function GetFromTest()
    {
        $headerContentType = 'Content-Type: application/json';
        $base_url = GetFromConfig("Test", "base_url_list");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($headerContentType));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $base_url . "/api/core/all_variables?auth_device_id=1");

        $output = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($output, true);

        return isset($output["Data"]) && is_array($output["Data"]) ? $output["Data"] : false;
    }

    static function UpdateInDB($variableList)
    {
        $countUpdated = 0;

        $stmt = GetStatement();

        $query = "";

        foreach ($variableList as $variable) {
            $currentVariable = self::GetFromDB(
                $variable["language_code"],
                $variable["type"],
                $variable["module"],
                $variable["template"],
                $variable["tag_name"]
            );
            $firstTag = explode("-", $variable["tag_name"])[0];

            if ((!isset($currentVariable["value"]) && $firstTag != "help_var") || $currentVariable["value"] == $variable["value"]) {
                continue;
            }

            $query = $firstTag == "help_var" ? "INSERT INTO language_variable (tag_name, value, type, module, template, language_code) VALUES (
                            " . Connection::GetSQLString($variable["tag_name"]) . ",
                            " . Connection::GetSQLString($variable["value"]) . ",
                            " . Connection::GetSQLString($variable["type"]) . ",
                            " . Connection::GetSQLString($variable["module"]) . ",
                            " . Connection::GetSQLString($variable["template"]) . ",
                            " . Connection::GetSQLString($variable["language_code"]) . ")
                        ON CONFLICT(tag_name, type, module, template, language_code) DO UPDATE
                            SET value=" . Connection::GetSQLString($variable["value"]) . ";" : "UPDATE language_variable
                        SET value=" . Connection::GetSQLString($variable["value"]) . "
                        WHERE tag_name=" . Connection::GetSQLString($variable["tag_name"]) . "
                            AND type=" . Connection::GetSQLString($variable["type"]) . "
                            AND module=" . Connection::GetSQLString($variable["module"]) . "
                            AND template=" . Connection::GetSQLString($variable["template"]) . "
                            AND language_code=" . Connection::GetSQLString($variable["language_code"]);

            $stmt->Execute($query);
            if ($stmt->GetAffectedRows() <= 0) {
                continue;
            }

            $countUpdated++;
        }

        return $countUpdated;
    }

    function LoadForJS($module = null)
    {
        $this->_LoadForPHP($module);

        return $this->_translatePHP["module" . $module];
    }

    function _LoadXML($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $parser = xml_parser_create("UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parse_into_struct($parser, implode("", file($file)), $this->_values, $this->_tags);
        xml_parser_free($parser);

        return true;
    }

    function _Convert(&$array)
    {
        $currentEncoding = strtoupper($this->_dataLanguageList[$this->_dataLanguageCode]["Encoding"]);
        if ($currentEncoding == "UTF-8" || defined("IS_ADMIN")) {
            return;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->_Convert($array[$key]);
            } else {
                if (function_exists('mb_convert_encoding')) {
                    $array[$key] = mb_convert_encoding($array[$key], $currentEncoding, "UTF-8");
                } else {
                    ErrorHandler::TriggerError("Function mb_convert_encoding() doesn't exist!", E_ERROR);
                }
            }
        }
    }

    function _LoadForPHP($module = null, $forceLanguage = null)
    {
        if (isset($this->_translatePHP["module" . $module]) && strlen($forceLanguage) == 0) {
            return;
        }

        if (strlen($forceLanguage) > 0) {
            $lang = $forceLanguage;
        } else {
            $lang = defined('IS_ADMIN') ? $this->_interfaceLanguageCode : $this->_dataLanguageCode;
        }

        $data = array();
        $files = array();

        $files[] = PROJECT_DIR . "language/" . $lang . "/_php.xml";
        $files[] = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . $lang . "/_php.xml";
        if (strlen($module) > 0) {
            $files[] = PROJECT_DIR . "language/" . $lang . "/" . $module . "_php.xml";
            $files[] = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . $lang . "/" . $module . "_php.xml";
        }
        if ($this->_CheckCache($files)) {
            $data = unserialize(fread($fp = fopen($this->_cacheFile, 'r'), filesize($this->_cacheFile)));
        } else {
            /*for ($i = 0; $i < count($files); $i++)
            {
                if ($this->_LoadXML($files[$i]))
                {
                    if (!isset($this->_tags["Root"][1]))
                        continue;
                    for ($j = $this->_tags["Root"][0] + 1; $j < $this->_tags["Root"][1]; $j++)
                    {
                        if (isset($this->_values[$j]["tag"]))
                        {
                            $attributeList = array();
                            if(isset($this->_values[$j]["attributes"]))
                            {
                                foreach ($this->_values[$j]["attributes"] as $k => $v)
                                    $attributeList[] = array("Title" => $k, "Value" => $v);
                            }
                            $data[$this->_values[$j]["tag"]] = array("Value" => isset($this->_values[$j]["value"]) ? PrepareContentBeforeShow($this->_values[$j]["value"]) : "",
                                                                                "AttributeList" => $attributeList);
                        }
                    }
                }
            }*/

            $variablesFromDB = self::GetFromDB($lang, "php", "core", "common");
            $variablesFromDBArray = array();
            foreach ($variablesFromDB as $variableFromDB) {
                $variablesFromDBArray[$variableFromDB["tag_name"]] = array(
                    "Value" => $variableFromDB["value"],
                    "AttributeList" => array(
                        "editable" => true,
                        "tag_name" => $variableFromDB["tag_name"],
                        "variable_id" => $variableFromDB["variable_id"]
                    )
                );
            }
            $data = array_merge($data, $variablesFromDBArray);

            if (strlen($module) > 0) {
                $variablesFromDB = self::GetFromDB($lang, "php", $module, "common");
                $variablesFromDBArray = array();
                foreach ($variablesFromDB as $variableFromDB) {
                    $variablesFromDBArray[$variableFromDB["tag_name"]] = array(
                        "Value" => $variableFromDB["value"],
                        "AttributeList" => array(
                            "editable" => true,
                            "tag_name" => $variableFromDB["tag_name"],
                            "variable_id" => $variableFromDB["variable_id"]
                        )
                    );
                }
                $data = array_merge($data, $variablesFromDBArray);
            }
            $this->_CreateCache(serialize($data));
        }

        $this->_translatePHP["module" . $module] = $data;
        $this->_Convert($this->_translatePHP["module" . $module]);
    }

    function _GetTemplateXMLAsArray()
    {
        $templates = array();
        foreach ($this->_values as $id => $value) {
            if ($value["level"] != 2) {
                continue;
            }

            $templates[$value["tag"]][$value["type"]] = $id;
        }

        $tmplArray = array();

        foreach ($templates as $name => $template) {
            if (isset($template["open"]) && isset($template["close"])) {
                for ($i = $template["open"] + 1; $i < $template["close"]; $i++) {
                    if ($this->_values[$i]["level"] == 3 && $this->_values[$i]["type"] == "complete") {
                        $attributeList = array();
                        if (isset($this->_values[$i]["attributes"])) {
                            foreach ($this->_values[$i]["attributes"] as $k => $v) {
                                $attributeList[] = array("Title" => $k, "Value" => $v);
                            }
                        }
                        $tmplArray[$name][$this->_values[$i]["tag"]] = array(
                            "Value" => isset($this->_values[$i]["value"]) ? PrepareContentBeforeShow($this->_values[$i]["value"]) : "",
                            "AttributeList" => $attributeList
                        );
                    }

                    if ($this->_values[$i]["level"] == 3 && $this->_values[$i]["type"] == "open") {
                        $file = $this->_values[$i]["tag"];
                    }

                    if ($this->_values[$i]["level"] != 4 || $this->_values[$i]["type"] != "complete") {
                        continue;
                    }

                    $attributeList = array();
                    if (isset($this->_values[$i]["attributes"])) {
                        foreach ($this->_values[$i]["attributes"] as $k => $v) {
                            $attributeList[] = array("Title" => $k, "Value" => $v);
                        }
                    }
                    $tmplArray[$name][$file][$this->_values[$i]["tag"]] = array(
                        "Value" => isset($this->_values[$i]["value"]) ? PrepareContentBeforeShow($this->_values[$i]["value"]) : "",
                        "AttributeList" => $attributeList
                    );
                }
            } else {
                $tmplArray[$name] = array();
            }
        }

        return $tmplArray;
    }

    function _CheckCache($xmlFiles)
    {
        if (is_array($xmlFiles) && count($xmlFiles) > 0) {
            $this->_cacheFile = $this->_GetFilename(implode("-", $xmlFiles));
            $maxFileTime = 0;
            for ($i = 0; $i < count($xmlFiles); $i++) {
                if (!is_file($xmlFiles[$i])) {
                    continue;
                }
                $t = filemtime($xmlFiles[$i]);
                if ($t <= $maxFileTime) {
                    continue;
                }

                $maxFileTime = $t;
            }

            if (file_exists($this->_cacheFile)) {
                if (
                    !(filemtime($this->_cacheFile) + $this->_cacheLifeTime < date('U') ||
                    filemtime($this->_cacheFile) < $maxFileTime)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    function _GetFilename($xmlFile)
    {
        return XML_CACHE_DIR . md5('XMLCachestaR' . $xmlFile) . '.xtc';
    }

    function _CreateCache($data)
    {
        if ($fp = fopen($this->_cacheFile, "w")) {
            flock($fp, 2); // set an exclusive lock
            fputs($fp, $data); // write the serialized array
            flock($fp, 3); // unlock file
            fclose($fp);
            touch($this->_cacheFile);
            @chmod($this->_cacheFile, 0666);

            return true;
        }

        return false;
    }

    function GetXML($module, $type)
    {
        $data = array();

        if ($type == 'php') {
            $file = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . DATA_LANGCODE . "/" . $module . "_php.xml";
            if ($this->_LoadXML($file) && is_array($this->_tags["Root"]) && count($this->_tags["Root"]) > 1) {
                for ($j = $this->_tags["Root"][0] + 1; $j < $this->_tags["Root"][1]; $j++) {
                    if (!isset($this->_values[$j]["tag"])) {
                        continue;
                    }

                    $attributeList = array();
                    if (isset($this->_values[$j]["attributes"])) {
                        foreach ($this->_values[$j]["attributes"] as $k => $v) {
                            $attributeList[] = array("Title" => $k, "Value" => $v);
                        }
                    }
                    $data[$this->_values[$j]["tag"]] = array(
                        "Value" => isset($this->_values[$j]["value"]) ? PrepareContentBeforeShow($this->_values[$j]["value"]) : "",
                        "AttributeList" => $attributeList
                    );
                }
            }
        } else {
            $file = PROJECT_DIR . "/language/" . DATA_LANGCODE . "/" . $module . "_template.xml";
            if ($this->_LoadXML($file)) {
                $data = $this->_GetTemplateXMLAsArray();
            }
        }

        return $data;
    }

    function SaveXML($module, $type, $data)
    {
        // Prepare content
        $xmlContent = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n<Root>\r\n";
        foreach ($data as $key => $value) {
            $xmlContent .= self::_SaveTag($key, $value);
        }
        $xmlContent .= "</Root>";

        // Save content
        $file = $type == 'php'
            ? PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . DATA_LANGCODE . "/" . $module . "_php.xml"
            : PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/language/" . DATA_LANGCODE . "/" . $module . "_template.xml";
        if (!$fp = fopen($file, "w")) {
            return;
        }

        flock($fp, 2); // set an exclusive lock
        fputs($fp, $xmlContent); // write XML content
        flock($fp, 3); // unlock file
        fclose($fp);
        touch($file);
        @chmod($file, 0666);
    }

    function _SaveTag($key, $value, $level = 0)
    {
        $indent = $indentString = "\t";
        for ($i = 0; $i < $level; $i++) {
            $indent .= $indentString;
        }

        if (is_array($value) && !isset($value["AttributeList"])) {
            $result = $indent . "<" . $key . ">\r\n";
            foreach ($value as $k => $v) {
                $result .= $this->_SaveTag($k, $v, $level + 1);
            }
            $result .= $indent . "</" . $key . ">\r\n";
        } else {
            $attributes = "";
            if (isset($value["AttributeList"])) {
                foreach ($value["AttributeList"] as $attr) {
                    $attributes .= " " . $attr["Title"] . "=\"" . $attr["Value"] . "\"";
                }
            }
            if (strlen($value["Value"]) > 0) {
                $result = preg_match("/[\n|<|>|\"|'|&]/", $value["Value"])
                    ? $indent . "<" . $key . $attributes . "><![CDATA[" . $value["Value"] . "]]></" . $key . ">\r\n"
                    : $indent . "<" . $key . $attributes . ">" . $value["Value"] . "</" . $key . ">\r\n";
            } else {
                $result = $indent . "<" . $key . $attributes . "></" . $key . ">\r\n";
            }
        }

        return $result;
    }
}
