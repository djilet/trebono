<?php
define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

$module = "product";
$moduleURL = "module.php?load=" . $module;

$result = array();

$user = new User();

if (!$user->LoadBySession() || !$user->ValidateAccess(array(
    "root",
    "company_unit" => null,
    "employee" => null,
    "contract" => null
), "or")) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));

    switch ($request->GetProperty("Action")) {
        case "GetOptionValueHistoryGlobalHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_option_value_history.html");

            $optionCode = Option::GetCodeByOptionID($request->GetProperty("option_id"));
            $optionTitleTranslation = GetTranslation("option-" . $optionCode, $module);
            $content->SetVar("option_title_translation", $optionTitleTranslation);

            $valueList = Option::GetOptionValueList(OPTION_LEVEL_GLOBAL, $request->GetProperty("option_id"), null);
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetOptionValueHistoryCompanyUnitHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_option_value_history.html");

            $optionCode = Option::GetCodeByOptionID($request->GetProperty("option_id"));
            $optionTitleTranslation = GetTranslation("option-" . $optionCode, $module);
            $content->SetVar("option_title_translation", $optionTitleTranslation);

            $valueList = Option::GetOptionValueList(OPTION_LEVEL_COMPANY_UNIT, $request->GetProperty("option_id"),
                $request->GetProperty("company_unit_id"));
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetOptionValueHistoryEmployeeHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_option_value_history.html");

            $optionCode = Option::GetCodeByOptionID($request->GetProperty("option_id"));
            $optionTitleTranslation = GetTranslation("option-" . $optionCode, $module);
            $content->SetVar("option_title_translation", $optionTitleTranslation);

            $valueList = Option::GetOptionValueList(OPTION_LEVEL_EMPLOYEE, $request->GetProperty("option_id"),
                $request->GetProperty("employee_id"), $content->GetVar("INTERFACE_LANGCODE"));
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetContractHistoryCompanyUnitHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_contract_history.html");

            $contractList = new ContractList($module);
            $contractList->LoadContractListByProductID(OPTION_LEVEL_COMPANY_UNIT,
                $request->GetProperty("company_unit_id"), $request->GetProperty("product_id"));
            $content->LoadFromObjectList("ContractList", $contractList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetContractPropertyHistoryCompanyUnitHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_contract_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = ContractList::GetPropertyValueList(OPTION_LEVEL_COMPANY_UNIT,
                $request->GetProperty("property_name"), $request->GetProperty("company_unit_id"),
                $request->GetProperty("product_id"));
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetContractHistoryEmployeeHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_contract_history.html");

            $contractList = new ContractList($module);
            $contractList->LoadContractListByProductID(OPTION_LEVEL_EMPLOYEE, $request->GetProperty("employee_id"),
                $request->GetProperty("product_id"));
            $content->LoadFromObjectList("ContractList", $contractList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetContractPropertyHistoryEmployeeHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_contract_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = ContractList::GetPropertyValueList(OPTION_LEVEL_EMPLOYEE,
                $request->GetProperty("property_name"), $request->GetProperty("employee_id"),
                $request->GetProperty("product_id"));
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "RemoveProductGroupImage":
            $productGroup = new ProductGroup($module);
            $productGroup->RemoveProductGroupImage($request->GetProperty("ItemID"),
                $request->GetProperty('SavedImage'));
            $result = "Done";
            break;
        case "GetPropertyHistoryAppVersionHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_option_value_history.html");

            $valueList = AppVersion::GetPropertyValueListAppVersion($request->GetProperty("property_name"),
                $request->GetProperty("app_version_id"));
            $content->SetLoop("ValueList", $valueList);
            $result["HTML"] = $popupPage->Grab($content);
            break;
    }
}

echo json_encode($result);
