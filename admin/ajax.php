<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$result = [];

$user = new User();
if (!$user->LoadBySession() || !$user->Validate(["root"])) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));
    switch ($request->GetProperty("Action")) {
        case "RemoveUserImage":
            $user = new User();
            $user->RemoveUserImage($request->GetProperty("ItemID"), $request->GetProperty("SavedImage"));
            $result = "Done";
            break;
        case "GetPropertyHistoryUserHTML":
            $popupPage = new PopupPage(null, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = User::GetPropertyValueListUser(
                $request->GetProperty("property_name"),
                $request->GetProperty("user_id")
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("ShowValue", $request->GetProperty("property_name") != "password");
            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetPermissionHistoryUserHTML":
            $popupPage = new PopupPage(null, true);
            $content = $popupPage->Load("block_permission_history.html");

            $valueList = User::GetPermissionValueListUser($request->GetProperty("user_id"));
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "SaveVariableToXML":
            $request->SetProperty("VariableValue", PrepareContentBeforeSave($request->GetProperty("VariableValue")));

            Language::SaveToDB($request->GetProperty("variable_id"), $request->GetProperty("VariableValue"));

            CleanCache("xml");
            $result = PrepareContentBeforeShow($request->GetProperty("VariableValue"));
            break;

        case "CreateVariable":
            $request->SetProperty("VariableValue", PrepareContentBeforeSave($request->GetProperty("VariableValue")));
            $language = GetLanguage();

            Language::CreateInDB(
                $language->_interfaceLanguageCode,
                "php",
                "core",
                "common",
                $request->GetProperty("tag_name"),
                $value = $request->GetProperty("VariableValue")
            );
            Language::CreateInDB(
                $language->_interfaceLanguageCode,
                "php",
                "core",
                "help",
                $request->GetProperty("tag_name"),
                $value = $request->GetProperty("label_value")
            );

            CleanCache("xml");
            $result = PrepareContentBeforeShow($request->GetProperty("VariableValue"));
            break;

        case "SetVariableEdit":
            $session = &GetSession();
            if ($request->GetProperty("Value")) {
                $session->SetProperty("EditVariables", true);
            } else {
                $session->RemoveProperty("EditVariables");
            }

            $session->SaveToDB();
            CleanCache("template");
            CleanCache("xml");
            break;

        case "GetVariable":
            $result = Language::GetByID($request->GetProperty("variable_id"));
            break;

        case "UpdateVariables":
            $variableList = Language::GetFromTest();
            $result = $variableList ? Language::UpdateInDB($variableList) : 0;

            CleanCache("xml");

            break;
        case "GetEmailTextHTML":
            $popupPage = new PopupPage(null, true);
            $content = $popupPage->Load("block_email_text.html");

            $fileName = Email::GetFileNameByID($request->GetProperty("email_id"));

            $filePath = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/mail/" . $fileName . ".txt";
            $fileStorage = GetFileStorage(CONTAINER__CORE);

            $content->SetVar("Title", $request->GetProperty("title"));
            $content->SetVar("Text", $fileStorage->GetFileContent($filePath));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetConfigHistoryHTML":
            $popupPage = new PopupPage(null, true);
            $content = $popupPage->Load("block_config_history.html");

            $config = new Config();
            $config->LoadByID($request->GetProperty("config_id"));
            $content->SetVar("editor", $config->GetProperty("editor"));
            $content->SetLoop("HistoryList", Config::GetConfigHistoryByID($config->GetProperty("config_id")));

            $configsWithValueColumn = ["agreement_of_sending_pdf_invoice"];
            if (
                $config->GetProperty("editor") == "field-float" ||
                in_array($config->GetProperty("code"), $configsWithValueColumn)
            ) {
                $content->SetVar("ShowValue", "Y");
            }

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetTranslation":
            $result = $request;
            break;
    }
}

echo json_encode($result);
