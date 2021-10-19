<?php

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");
require_once(dirname(__FILE__) . "/include/contract.php");
require_once(dirname(__FILE__) . "/include/confirmation.php");

$module = "agreements";
$moduleURL = ADMIN_PATH . "module.php?load=" . $module;

$result = [];
$request = new LocalObject(array_merge($_GET, $_POST));

$user = new User();
if ($request->GetProperty("agreement_id") && $request->GetProperty("Action") == "GetPropertyHistoryAgreementHTML") {
    $agreement = new AgreementsContract($module);
    $agreement->LoadByID($request->GetProperty("agreement_id"));

    $request->SetProperty("OrganizationID", $agreement->GetProperty("organization_id"));
}

if (!$user->LoadBySession() || !$user->ValidateAccess([
		"company_unit" => $request->GetIntProperty("OrganizationID"),
		"contract" => $request->GetIntProperty("OrganizationID")
    ], "or"
)) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
}

switch ($request->GetProperty("Action")) {
    case "GetAgreementContractHistoryHTML":
        $popupPage = new PopupPage($module, true);
        $content = $popupPage->Load("partial/agreements_history_list.html");

        $agreementContract = new AgreementsContract($module);
        $historyList = $agreementContract->GetHistoryList(
            $request->GetIntProperty("AgreementID"),
            $request->GetIntProperty("version")
        );

        $urlFilter = new URLFilter();
        $urlFilter->LoadFromObject($request, ["OrganizationID", "AgreementID"]);

        $content->SetLoop("HistoryList", $historyList);
        $content->SetVar("MODULE_URL", $moduleURL);
        $content->SetVar("URL_PARAMS", $urlFilter->GetForURL());

        $result["HTML"] = $popupPage->Grab($content);
        break;
    case "GetPropertyHistoryAgreementHTML":
        $popupPage = new PopupPage($module, true);
        $content = $popupPage->Load("partial/block_property_history.html");

        $valueList = AgreementsContract::GetPropertyValueListAgreement(
            $request->GetProperty("property_name"),
            $request->GetProperty("agreement_id")
        );
        $content->SetLoop("ValueList", $valueList);
        $result["HTML"] = $popupPage->Grab($content);
        break;
    case "GetConfirmationHistoryHTML":
        $popupPage = new PopupPage($module, true);
        $content = $popupPage->Load("partial/confirmation_history_list.html");

        $confirmation = new RecreationConfirmation($module);
        $historyList = $confirmation->GetHistoryList(
            $request->GetIntProperty("ConfirmationID")
        );

        $urlFilter = new URLFilter();
        $urlFilter->LoadFromObject($request, ["OrganizationID"]);

        $content->SetLoop("HistoryList", $historyList);
        $content->SetVar("MODULE_URL", $moduleURL);
        $content->SetVar("URL_PARAMS", $urlFilter->GetForURL());

        $result["HTML"] = $popupPage->Grab($content);
        break;
}


echo json_encode($result);
