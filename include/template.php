<?php

es_include("vlibtemplate/vlibtemplate.php");

class Template extends VLibTemplateCache
{

    function Template($tmplFile = null, $options = null)
    {
        parent::VLibTemplate($tmplFile, $options);

        // date without time
        $this->formatTags['date'] = array('open' => '$this->_FormatDate(', 'close' => ')');
        $this->formatTags['datepicker'] = array('open' => '$this->_FormatDatepicker(', 'close' => ')');
        $this->formatTags['date_recurring'] = array('open' => '$this->_FormatDateRecurring(', 'close' => ')');
        $this->formatTags['date_implementation'] = array('open' => '$this->_FormatDateImplementation(', 'close' => ')');
        $this->formatTags['date_billing'] = array('open' => '$this->_FormatDateBilling(', 'close' => ')');
        $this->formatTags['date_german'] = array('open' => '$this->_FormatDateGerman(', 'close' => ')');
        $this->formatTags['date_billing_period'] = array('open' => '$this->_FormatDateBillingPeriod(', 'close' => ')');

        // date with time (include seconds
        $this->formatTags['datetimefull'] = array('open' => '$this->_FormatDateTimeFull(', 'close' => ')');

        // date with time
        $this->formatTags['datetime'] = array('open' => '$this->_FormatDate(', 'close' => ',true)');

        // time
        $this->formatTags['time'] = array('open' => '$this->_FormatTime(', 'close' => ')');

        // rfc8222 date
        $this->formatTags['rfc2822'] = array('open' => '$this->_FormatRFC8222(', 'close' => ')');

        // price
        $this->formatTags['price'] = array('open' => '$this->_FormatPrice(', 'close' => ')');

        // integer
        $this->formatTags['integer'] = array('open' => "intval(", 'close' => ")");

        /**@var language Language */
        $language =& GetLanguage();

        // Data language
        $this->SetVar("DATA_LANGCODE", DATA_LANGCODE);
        $this->SetVar("DATA_LANGNAME", $language->GetDataLanguageName());
        $lngList = $language->GetDataLanguageList();
        if (count($lngList) > 1) {
            $this->SetLoop("DataLanguageList", array_values($lngList));
        }

        // Interface language
        $this->SetVar("INTERFACE_LANGCODE", INTERFACE_LANGCODE);
        $this->SetVar("INTERFACE_LANGNAME", $language->GetInterfaceLanguageName());
        $lngList = $language->GetInterfaceLanguageList();
        if (count($lngList) > 1) {
            $this->SetLoop("InterfaceLanguageList", array_values($lngList));
        }

        $this->SetVar("CHARSET", $language->GetHTMLCharset());
        $this->SetVar("PROJECT_PATH", PROJECT_PATH);
        $this->SetVar("ADMIN_PATH", ADMIN_PATH);
        $this->SetVar("URL_PREFIX", GetUrlPrefix());
        $this->SetVar("INDEX_PAGE", INDEX_PAGE);
        $this->SetVar("HTML_EXTENSION", HTML_EXTENSION);
        $this->SetVar("WEBSITE_FOLDER", WEBSITE_FOLDER);
        $this->SetVar("WEBSITE_NAME", WEBSITE_NAME);
        $this->SetVar("DEV_MODE", GetFromConfig('DevMode', 'common'));
        $this->SetVar("PROD_BASE_URL", GetFromConfig("Production", "base_url_list"));

        $session =& GetSession();
        $user = $session->GetProperty("LoggedInUser");
        if (!is_array($user)) {
            return;
        }

        // Do not show website list for users which are assigned to the website
        if (is_array($GLOBALS["WebsiteList"]) && count($GLOBALS["WebsiteList"]) > 1 && !isset($user["WebsiteID"])) {
            $this->SetLoop("WebsiteList", $GLOBALS["WebsiteList"]);
        }

        foreach ($user as $k => $v) {
            $this->SetVar("USER_" . $k, $v);
        }
    }

    function LoadFromArray($data)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->SetLoop($k, $v);
            } else {
                $this->SetVar($k, $v);
            }
        }
    }

    function LoadFromObject($object, $properties = array())
    {
        if (is_array($properties) && count($properties) > 0) {
            for ($i = 0; $i < count($properties); $i++) {
                $v = $object->GetProperty($properties[$i]);

                if (is_array($v)) {
                    $this->SetLoop($properties[$i], $v);
                } else {
                    $this->SetVar($properties[$i], $v);
                }
            }
        } else {
            $this->LoadFromArray($object->GetProperties());
        }
    }

    function SetLoop($k, $v)
    {
        // TODO: Create warning
        $result = true;

        if (is_array($v)) {
            for ($i = 0; $i < count($v); $i++) {
                if (!isset($v[$i]) || !is_array($v[$i])) {
                    $result = false;
                    break;
                }
            }
        } else {
            $result = false;
        }

        if (!$result) {
            return;
        }

        parent::SetLoop($k, $v);
    }

    function LoadFromObjectList($name, $object)
    {
        $this->SetLoop($name, $object->GetItems());
    }

    function LoadErrorsFromObject($object)
    {
        $this->SetLoop("ErrorList", $object->GetErrorsAsArray());
    }

    function LoadMessagesFromObject($object)
    {
        $this->SetLoop("MessageList", $object->GetMessagesAsArray());
    }

    function LoadErrorFieldsFromObject($object)
    {
        $this->SetLoop("ErrorFieldList", $object->GetErrorFieldsAsArray());
    }

    function LoadTemplateList($template = "")
    {
        $templateDir = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/";
        $templateList = array();
        if ($dh = opendir($templateDir)) {
            while (($file = readdir($dh)) !== false) {
                if (!preg_match("/^page(.*)\.html$/", $file)) {
                    continue;
                }

                if (substr($file, 5, -5) == '') {
                    $templateList[] = array(
                        "FileName" => $file,
                        "Template" => GetTranslation('template-general'),
                        "Selected" => ($file == $template)
                    );
                } else {
                    $templateList[] = array(
                        "FileName" => $file,
                        "Template" => GetTranslation('template-' . substr($file, 5, -5)),
                        "Selected" => ($file == $template)
                    );
                }
            }
            closedir($dh);
        }
        if (count($templateList) > 1) {
            $this->SetLoop("TemplateList", $templateList);
        } else {
            if (count($templateList) == 1) {
                $this->SetVar("TemplateOne", $templateList[0]['Template']);
                $this->SetVar("Template", $templateList[0]['FileName']);
            } else {
                $this->SetLoop("ErrorList", array(
                    0 => array(
                        'Message' => GetTranslation(
                            'no-templates',
                            array('Folder' => PROJECT_PATH . "website/" . WEBSITE_FOLDER . "/template/")
                        )
                    )
                ));
            }
        }
    }


    function GetTemplateSets($module, $set = "")
    {
        $templateDir = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/";
        $templateSets = array();
        $l = strlen($module);
        if ($dh = opendir($templateDir)) {
            while (($file = readdir($dh)) !== false) {
                if (!is_dir($templateDir . $file) || substr($file, 0, $l) != $module) {
                    continue;
                }

                $cSet = substr($file, $l + 1);
                $templateSets[] = array(
                    "SetName" => $cSet,
                    "SetTitle" => GetTranslation($cSet . '-title', $module),
                    "Selected" => ($cSet == $set)
                );
            }
            closedir($dh);
        }

        return $templateSets;
    }

    function LoadModuleTemplateSets($module, $set = "")
    {
        $templateSets = $this->GetTemplateSets($module, $set);

        if (count($templateSets) > 1) {
            $this->SetLoop("TemplateSets", $templateSets);
        } else {
            if (count($templateSets) == 1) {
                $this->SetVar("Template", $templateSets[0]["SetName"]);
            } else {
                $this->SetVar("Template", "");
            }
        }
    }

    function _FormatDateTimeFull($date)
    {
        if (empty($date)) {
            return null;
        }

        return date('d.m.Y H:i:s', strtotime($date));
    }

    function _FormatDate($date, $showTime = false)
    {
        if (empty($date)) {
            return null;
        }

        $language =& GetLanguage();

        $format = $showTime
            ? $language->GetDateFormat() . " " . $language->GetTimeFormat()
            : $language->GetDateFormat();

        return LocalDate($format, strtotime($date));
    }

    function _FormatDatepicker($date)
    {
        if (empty($date)) {
            return null;
        }

        /* return date("j, F Y", strtotime($date)); */
        return date('d.m.Y', strtotime($date));
    }

    function _FormatDateRecurring($date)
    {
        if (empty($date)) {
            return null;
        }

        return date('j.n.y', strtotime($date));
    }

    function _FormatDateImplementation($date)
    {
        if (empty($date)) {
            return null;
        }

        return GetGermanMonthName(date('m', strtotime($date)));
    }

    function _FormatDateGerman($date)
    {
        if (empty($date)) {
            return null;
        }

        $date = strtotime($date);
        $j = date("j", $date);
        $m = date("m", $date);
        $m = GetGermanMonthName($m);
        $y = date("Y", $date);

        return $j . ". " . $m . " " . $y;
    }

    function _FormatDateBilling($date)
    {
        if (empty($date)) {
            return null;
        }

        return date('jS F Y', strtotime($date));
    }

    function _FormatDateBillingPeriod($date)
    {
        if (empty($date)) {
            return null;
        }

        return date('F jS Y', strtotime($date));
    }

    function _FormatTime($date)
    {
        if (empty($date)) {
            return null;
        }

        $language =& GetLanguage();
        $format = $language->GetTimeFormat();

        return LocalDate($format, strtotime($date));
    }

    function _FormatRFC8222($date)
    {
        if (empty($date)) {
            return null;
        }

        return date("r", strtotime($date));
    }

    function _FormatPrice($number)
    {
        if (strlen($number) === 0) {
            return null;
        }

        return number_format($number, 2, ",", ".");
    }
}
