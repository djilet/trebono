<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(array("root"));

$request = new LocalObject(array_merge($_GET, $_POST));

if ($request->GetProperty("Action") == "GetInsert") {
    $variableResultArray = array();
    foreach ($request->GetProperty("VariableIDs") as $varID) {
        $variable = Language::GetByID2($varID);
        $variableResultArray = array_merge(
            $variableResultArray,
            Language::GetFromDB(
                false,
                $variable["type"],
                $variable["module"],
                $variable["template"],
                $variable["tag_name"]
            )
        );
    }

    $query = "";
    foreach ($variableResultArray as $variable) {
        $query .= '$this->langVarList[] = new LangVar("'
            . $variable["language_code"] . '", "'
            . $variable["type"] . '", "'
            . $variable["module"] . '", "'
            . $variable["template"] . '", "'
            . $variable["tag_name"] . '", "'
            . $variable["value"] . '");
';
    }
    echo json_encode($query);
    exit();
}

if ($request->GetProperty("Action") == "GetDelete") {
    $variableResultArray = array();
    foreach ($request->GetProperty("VariableIDs") as $varID) {
        $variable = Language::GetByID2($varID);
        $variableResultArray = array_merge(
            $variableResultArray,
            Language::GetFromDB(
                false,
                $variable["type"],
                $variable["module"],
                $variable["template"],
                $variable["tag_name"]
            )
        );
    }

    $query = "";
    foreach ($variableResultArray as $variable) {
        $query .= '$this->delLangVarList[] = new LangVar("' .
            $variable["language_code"] . '", "' .
            $variable["type"] . '", "' .
            $variable["module"] . '", "' .
            $variable["template"] . '", "' .
            $variable["tag_name"] . '", "' .
            $variable["value"] . '");
';
    }
    echo json_encode($query);
    exit();
}

if ($request->GetProperty("Action") == "GetTemplate") {
    $template = 'private $langVarList = array();
private $delLangVarList = array();
    
    public function init()
    {
        
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }';
    echo json_encode($template);
    exit();
}

if ($request->GetProperty("Action") == "GetInsertQuery") {
    $query = "";
    $valueList = $request->GetProperty("Value");
    foreach ($valueList as $langCode => $value) {
        if ($langCode == "de" && empty($value) && !empty($valueList["en"])) {
            $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?' .
                'key=trnsl.1.1.20190421T160238Z.e0aeaf5bf3768e5d.8cad784cac0ac61d58bee4722a6dbccdb23aceaf&' .
                'text=' . urlencode($valueList["en"]) . '&' .
                'lang=en-de&' .
                'format=plain';

            $curlObject = curl_init();

            curl_setopt($curlObject, CURLOPT_URL, $url);

            curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);

            $responseData = curl_exec($curlObject);

            curl_close($curlObject);

            $responseData = json_decode($responseData, true);

            if (!empty($responseData["text"][0])) {
                $valueList["de"] = $responseData["text"][0];
            } else {
                echo false;
            }
        }
        //if($value != "")
        //{
        $query .= '$this->langVarList[] = new LangVar("'
            . $langCode . '", "'
            . $request->GetProperty("type") . '", "'
            . $request->GetProperty("module") . '", "'
            . $request->GetProperty("template") . '", "'
            . $request->GetProperty("tag_name") . '", "'
            . $valueList[$langCode] . '");
';
        //}
    }
    $query .= '$this->langVarList[] = new LangVar("tr", "'
        . $request->GetProperty("type") . '", "'
        . $request->GetProperty("module") . '", "'
        . $request->GetProperty("template") . '", "'
        . $request->GetProperty("tag_name") . '", "'
        . $valueList["de"] . '");';
    echo json_encode($query);
    exit();
}
$adminPage = new AdminPage();

$title = GetTranslation('admin-menu-template-variables');
$javaScripts = array(
    array("JavaScriptFile" => ADMIN_PATH . "template/js/variable.js"),
    array("JavaScriptFile" => CKEDITOR_PATH . "ckeditor.js"),
    array("JavaScriptFile" => CKEDITOR_PATH . "ajexFileManager/ajex.js")
);
$navigation = array(
    array("Title" => $title, "Link" => "variable.php")
);
$header = array(
    "Title" => $title,
    "Navigation" => $navigation,
    "JavaScripts" => $javaScripts
);
$content = $adminPage->Load("variable.html", $header);
$content->SetVar("Title", $title);

$language = GetLanguage();

$k = 0;
$sections = array();
$sectionsTmp = array();

$module = new Module();
$moduleList = $module->GetModuleList();

$sectionsTmp[] = array("Folder" => "core", "Title" => "Core");
$sectionsTmp = array_merge($sectionsTmp, $moduleList);

$i = 0;
foreach ($sectionsTmp as $k => $section) {
    $sections[$i] = $section;
    $sections[$i + 1] = $section;
    $sections[$i]["Section"] = $sections[$i]["Folder"] . "-php";
    $sections[$i + 1]["Section"] = $sections[$i + 1]["Folder"] . "-template";

    $sections[$i]["Title"] .= " php";
    $sections[$i + 1]["Title"] .= " template";

    if ($sections[$i]["Section"] == $request->GetProperty("section")) {
        $sections[$i]["Selected"] = true;
    }

    if ($sections[$i + 1]["Section"] == $request->GetProperty("section")) {
        $sections[$i + 1]["Selected"] = true;
    }

    $i += 2;
}
if (!$request->GetProperty("section")) {
    $request->SetProperty("section", $sections[0]["Section"]);
}

$sectionExplode = explode("-", $request->GetProperty("section"));

$variablesFromDB = Language::GetFromDB($language->_interfaceLanguageCode, $sectionExplode[1], $sectionExplode[0]);
$templateListTmp = array();
foreach ($variablesFromDB as $variableFromDB) {
    $key = array_search($variableFromDB["template"], array_column($templateListTmp, "template"));
    if ($key !== false) {
        $templateListTmp[$key]["variable_list"][] = $variableFromDB;
    } else {
        $templateListTmp[] = array(
            "template" => $variableFromDB["template"],
            "variable_list" => array($variableFromDB)
        );
    }
}

$content->SetVar("module", $sectionExplode[0]);
$content->SetVar("type", $sectionExplode[1]);

$content->SetLoop("LanguageList", $language->_interfaceLanguageList);

$content->SetLoop("SectionList", $sections);
$content->SetLoop("VariableList", $templateListTmp);
$content->SetVar("SelectedSection", $request->GetProperty('section'));

$content->SetVar('TagName', GetTranslation('tag-name'));
$content->SetVar('VariableValue', GetTranslation('variable-value'));

$adminPage->Output($content);
