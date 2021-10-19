<?php
$user->ValidateAccess(array("root"));

$navigation[] = array(
    "Title" => GetTranslation("section-product_group", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

if ($request->GetProperty("group_id")) {
    $title = GetTranslation("title-product_group-edit", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&group_id=" . $request->GetProperty("group_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => array(),
        "JavaScripts" => array()
    );

    $content = $adminPage->Load("product_group_edit.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&group_id=" . $request->GetProperty("group_id");
    Operation::Save($link, "product_group", "product_group_view", $request->GetProperty("group_id"));

    $productGroup = new ProductGroup($module);
    if ($request->GetProperty("Save")) {
        $productGroup->LoadFromObject($request);
        if ($productGroup->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&group_id=" . $productGroup->GetProperty("group_id");
            Operation::Save($link, "product_group", "product_group_save", $productGroup->GetProperty("group_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);

            $productGroup->LoadByID($request->GetProperty("group_id"));
            //exit();
        } else {
            $content->LoadErrorsFromObject($productGroup);
        }
    } else {
        $productGroup->LoadByID($request->GetProperty("group_id"));
    }

    $receiptTypeList = new ReceiptTypeList($module);
    $receiptTypeList->LoadReceiptTypeListForAdmin(false);
    $selectedReceiptTypeCodes = array_column($productGroup->GetProperty("ReceiptTypeList"), "code");
    foreach ($receiptTypeList->_items as $key => $receiptType) {
        if (in_array($receiptType["code"], $selectedReceiptTypeCodes)) {
            $receiptTypeList->_items[$key]["selected"] = 1;
        }
    }
    $content->LoadFromObjectList("ReceiptTypeListSelect", $receiptTypeList);

    $content->LoadFromObject($productGroup);
    $content->SetLoop("ProductGroupImageParamList", $productGroup->GetImageParams("product_group"));
} elseif ($request->IsPropertySet("receipt_type_id")) {
    $title = GetTranslation("title-receipt_type", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_type_id=" . $request->GetProperty("receipt_type_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => array(),
        "JavaScripts" => array()
    );

    $content = $adminPage->Load("receipt_type_edit.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_type_id=" . $request->GetProperty("receipt_type_id");
    Operation::Save($link, "product_group", "receipt_type_view", $request->GetIntProperty("receipt_type_id"));

    $receiptType = new ReceiptType($module);
    $receiptType->LoadByID($request->GetProperty("receipt_type_id"));
    if ($request->GetProperty("Save")) {
        $receiptType->AppendFromObject($request);
        if ($receiptType->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_type_id=" . $receiptType->GetProperty("receipt_type_id");
            Operation::Save($link, "product_group", "receipt_type_save", $receiptType->GetProperty("receipt_type_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);

            $receiptType->LoadByID($receiptType->GetProperty("receipt_type_id"));
            //exit();
        } else {
            $content->LoadErrorsFromObject($receiptType);
        }
    }

    if (!$receiptType->GetProperty("receipt_type_id")) {
        $language = GetLanguage();
        $languageList = array_values($language->GetInterfaceLanguageList());

        if ($translationList = $request->GetProperty("translation_list")) {
            foreach ($languageList as $key => $lang) {
                if (isset($translationList[$lang["Folder"]])) {
                    $languageList[$key]["translation"] = $translationList[$lang["Folder"]];
                }
            }
        }
        $content->SetLoop("LanguageList", $languageList);
    }

    $content->LoadFromObject($receiptType);
    $content->SetLoop("ReceiptTypeImageParamList", $receiptType->GetImageParams("receipt_type"));
} elseif ($request->IsPropertySet("app_version_id")) {
    if ($request->GetProperty("voucher_id") > 0) {
        $title = GetTranslation("title-app_version-add", $module);
    } else {
        $title = GetTranslation("title-app_version-edit", $module);
    }

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&app_version_id=" . $request->GetProperty("app_version_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => array(),
        "JavaScripts" => array()
    );

    $content = $adminPage->Load("app_version_edit.html", $header);

    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&app_version_id=" . $request->GetProperty("app_version_id");
    Operation::Save($link, "product_group", "app_version_view", $request->GetIntProperty("app_version_id"));

    $appVersion = new AppVersion();
    if ($request->GetProperty("Save")) {
        $appVersion->LoadFromObject($request);
        if ($appVersion->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&app_version_id=" . $appVersion->GetProperty("app_version_id");
            Operation::Save($link, "product_group", "app_version_save", $appVersion->GetProperty("app_version_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            $content->LoadErrorsFromObject($appVersion);
        }
    } else {
        $appVersion->LoadByID($request->GetProperty("app_version_id"));
    }

    $content->LoadFromObject($appVersion);
} else {
    $header = array(
        "Title" => GetTranslation("section-product_group", $module),
        "Navigation" => $navigation,
        "StyleSheets" => array(),
        "JavaScripts" => array()
    );

    $content = $adminPage->Load("product_group_list.html", $header);
    $content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));
    $content->SetVar("LNG_ActivateMessage", GetTranslation("confirm-activate", "core"));

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "product_group", "product_group_list");

    if ($request->GetProperty('Do') == 'RemoveAppVersion' && $request->GetProperty("AppVersionIDs")) {
        $appVersionList = new AppVersionList();
        $appVersionList->Remove($request->GetProperty("AppVersionIDs"));
        $content->LoadMessagesFromObject($appVersionList);
        $content->LoadErrorsFromObject($appVersionList);
        Operation::Save($link, "product_group", "app_version_delete");
    }

    if ($request->GetProperty('Do') == 'Remove' && $request->GetProperty("ReceiptTypeIDs")) {
        $receiptTypeList = new ReceiptTypeList($module);
        $receiptTypeList->Remove($request->GetProperty("ReceiptTypeIDs"));
        $content->LoadMessagesFromObject($receiptTypeList);
        $content->LoadErrorsFromObject($receiptTypeList);
        Operation::Save($link, "product", "receipt_type_delete");
    }
    if ($request->GetProperty('Do') == 'Activate' && $request->GetProperty("ReceiptTypeIDs")) {
        $receiptTypeList = new ReceiptTypeList($module);
        $receiptTypeList->Activate($request->GetProperty("ReceiptTypeIDs"));
        $content->LoadMessagesFromObject($receiptTypeList);
        $content->LoadErrorsFromObject($receiptTypeList);
        Operation::Save($link, "receipt", "receipt_type_activate");
    }

    $productGroupList = new ProductGroupList($module);
    $productGroupList->LoadProductGroupListForAdmin();
    $content->LoadFromObjectList("ProductGroupList", $productGroupList);

    $receiptTypeList = new ReceiptTypeList($module);
    $receiptTypeList->LoadReceiptTypeListForAdmin(false);
    $content->LoadFromObjectList("ReceiptTypeList", $receiptTypeList);

    $appVersionList = new AppVersionList();
    $appVersionList->SetOrderBy("app_version_desc");
    $appVersionList->LoadAppVersionList();
    $content->LoadFromObjectList("AppVersionList", $appVersionList);

    $langTypeList = array(
        array("postfix" => "-api-confirmation_description"),
        array("postfix" => "-api-receipt_approve_by_employee_success")
    );
    foreach ($langTypeList as $langKey => $langType) {
        $langTypeList[$langKey]["type_translation"] = GetTranslation($langTypeList[$langKey]["postfix"], $module);

        foreach ($productGroupList->GetItems() as $product) {
            $langTypeList[$langKey]["ProductGroupLangList"][] = array(
                "product_group_title" => $product["title_translation"],
                "value" => GetTranslation($product["code"] . $langTypeList[$langKey]["postfix"], $module)
            );
            if ($product["code"] == PRODUCT_GROUP__TRAVEL && $langTypeList[$langKey]["postfix"] == "-api-receipt_approve_by_employee_success") {
                $langTypeList[$langKey]["ProductGroupLangList"][] = array(
                    "product_group_title" => $product["title_translation"] . " " . GetTranslation("option-" . OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
                            "product"),
                    "value" => GetTranslation($product["code"] . $langTypeList[$langKey]["postfix"] . "-daily-allowance",
                        $module)
                );
            }
        }
    }
    $content->SetLoop("ProductGroupLangTypeList", $langTypeList);

    $miscLangList = [];
    $miscLangList[] = array(
        "description" => GetTranslation("api-error-trip-finished-description", "product"),
        "translation" => GetTranslation("api-error-trip-finished", "company")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("no-more-login-attempts-description", "product"),
        "translation" => GetTranslation("no-more-login-attempts")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("api-delete-receipt-button-description", "receipt"),
        "translation" => GetTranslation("api-delete-receipt-button", "receipt")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("api-delete-receipt-question-description", "receipt"),
        "translation" => GetTranslation("api-delete-receipt-question", "receipt")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("reset-password-error-deactivated-user-description"),
        "translation" => GetTranslation("reset-password-error-deactivated-user")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("no-available-vouchers-description", "product"),
        "translation" => GetTranslation("no-available-vouchers", "company")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("preferred-voucher-category-select-description", "product"),
        "translation" => GetTranslation("preferred-voucher-category-select", "company")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("api-recreation-confirmation-popup-description", "product"),
        "translation" => GetTranslation("api-recreation-confirmation-popup", "product")
    );
    $miscLangList[] = array(
        "description" => GetTranslation("api-add-picture-from-gallery-button-description", "receipt"),
        "translation" => GetTranslation("api-add-picture-from-gallery-button", "receipt"),
    );
    $content->SetLoop("MiscLangList", $miscLangList);

    $configList = new ConfigList();
    $configList->LoadConfigList("mobile_app");
    $content->LoadFromObjectList("ConfigList", $configList);
}