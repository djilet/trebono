<?php

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

$module = "billing";
$moduleURL = "module.php?load=" . $module;

$result = array();

$user = new User();
if (!$user->LoadBySession()) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));

    switch ($request->GetProperty("Action")) {
        case "GetPropertyHistoryInvoiceHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $valueList = Invoice::GetPropertyValueListInvoice(
                $request->GetProperty("property_name"),
                $request->GetProperty("invoice_id"),
                true
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("HideValue", $request->GetProperty("property_name") == "password");
            $content->SetVar("View", "Y");
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetPropertyHistoryStoredDataHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $valueList = StoredData::GetPropertyValueListStoredData(
                $request->GetProperty("property_name"),
                $request->GetProperty("stored_data_id"),
                true
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("HideValue", $request->GetProperty("property_name") == "password");
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetPropertyHistoryBookkeepingExportHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $valueList = BookkeepingExport::GetPropertyValueListBookkeepingExport(
                $request->GetProperty("property_name"),
                $request->GetProperty("export_id"),
                true
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("HideValue", $request->GetProperty("property_name") == "password");
            $result["HTML"] = $popupPage->Grab($content);
            break;
    }
}

echo json_encode($result);
