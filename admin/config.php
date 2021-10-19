<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
//validate access only for config_list and config_edit. history of some configs has public access

$request = new LocalObject(array_merge($_GET, $_POST));

$adminPage = new AdminPage();

$navigation = [
    ["Title" => GetTranslation("title-config-list"), "Link" => "config.php"],
];

$sectionList = ["ocr", "push", "others"];

if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", $sectionList[0]);
}

$templateSectionList = [];
foreach ($sectionList as $section) {
    $sectionTitle = GetTranslation("section-" . $section);
    $templateSectionList[] = [
        "Section" => $section,
        "Title" => $sectionTitle,
        "Selected" => ($request->GetProperty("Section") == $section ? 1 : 0),
    ];
}

if ($request->GetProperty("config_version_id")) {
    $configHistory = Config::GetConfigHistoryValue($request->GetProperty("config_version_id"));
    $config = new Config();
    $config->LoadByID($configHistory["config_id"]);

    if (
        in_array($config->GetProperty("code"), [
        "business_terms_1",
        "business_terms_2",
        "business_terms_3",
        "business_terms_4",
        "business_terms_5",
        "business_terms_6",
        ])
    ) {
        $fileName = $configHistory["value"];
        $filePath = CONFIG_FILE_DIR . $fileName;
        OutputFile($filePath, CONTAINER__CORE, $fileName);
    } elseif (!in_array($config->GetProperty("code"), ["app_license", "app_guideline", "app_org_guideline"])) {
        Send403();
    }

    $title = GetTranslation(
        "title-config-value-view",
        null,
        ["title_translation" => $config->GetProperty("title_translation")]
    );
    $header = [
        "Title" => $title,
        "Navigation" => $navigation,
    ];

    $content = $adminPage->Load("config_history_value.html", $header);
    $content->LoadFromArray($configHistory);
    $content->SetVar("config_title_translation", $config->GetProperty("title_translation"));
} elseif ($request->GetProperty("config_id")) {
    if ($request->GetProperty("Action") == "DownloadPDF") {
        $auth->ValidateAccess(["root", "company_unit" => null, "contract" => null], "or");
    } else {
        $auth->ValidateAccess(["root"]);
    }

    $javaScripts = [
        ["JavaScriptFile" => CKEDITOR_PATH . "ckeditor.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
    ];
    $styleSheets = [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
    ];

    $navigation[] = [
        "Title" => GetTranslation("title-config-edit"),
        "Link" => "config.php?config_id=" . $request->GetProperty("config_id"),
    ];
    $header = [
        "Title" => GetTranslation("title-config-edit"),
        "Navigation" => $navigation,
        "StyleSheets" => $styleSheets,
        "JavaScripts" => $javaScripts,
    ];
    $content = $adminPage->Load("config_edit.html", $header);

    $link = "config.php?config_id=" . $request->GetProperty("config_id");
    Operation::Save($link, "config", "config_id", $request->GetProperty("config_id"));

    $config = new Config();
    $config->LoadByID($request->GetProperty("config_id"));

    if (
        in_array($config->GetProperty("code"), [
        "invoice_vat",
        "voucher_invoice_vat",
        "receipt_verificator_payment_review",
        "receipt_verificator_payment_supervisor",
        "receipt_verificator_payment_approve_proposed",
        "receipt_verificator_payment_denied",
        ])
    ) {
        $user = new User();
        $user->LoadBySession();
        if ($user->Validate(["root"])) {
            $content->SetVar("Admin", "Y");
        }
        $content->SetVar("DateOfParams", date("Y-m-d"));
    }

    if ($request->GetProperty("Action") == "DownloadPDF") {
        $fileName = $config->GetProperty("value");
        $filePath = CONFIG_FILE_DIR . $fileName;
        OutputFile($filePath, CONTAINER__CORE, $fileName);
    }
    if ($request->GetProperty("Save")) {
        $config->SetProperty("value", $request->GetProperty("value"));

        if ($request->IsPropertySet("config_file")) {
            $config->SetProperty("config_file", $request->GetProperty("config_file"));
        }
        if ($request->IsPropertySet("saved_config_file")) {
            $config->SetProperty("saved_config_file", $request->GetProperty("saved_config_file"));
        }
        if ($request->IsPropertySet("date_from")) {
            $config->SetProperty("date_from", $request->GetProperty("date_from"));
        }

        if ($config->Save()) {
            //header("Location: ".urldecode($request->GetProperty("return_path")));
            Operation::Save($link, "config", "config_id_save", $request->GetProperty("config_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            $content->LoadErrorsFromObject($config);
        }
    }

    $content->LoadFromObject($config);
    $content->SetVar("ReturnPath", urldecode($request->GetProperty("return_path")));

    if ($config->GetProperty("group_code") == "email_texts") {
        $properties = [
            "base_url",
            "company-title",
            "company-reg_email_text",
            "salutation",
            "first_name",
            "last_name",
            "email",
            "password",
        ];
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, "company")
            );
        }
        $content->SetLoop("Replacements", $replacements);
    }
} else {
    $auth->ValidateAccess(["root"]);

    $javaScripts = [
        ["JavaScriptFile" => CKEDITOR_PATH . "ckeditor.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
    ];

    $header = [
        "Title" => GetTranslation("title-config-list"),
        "Navigation" => $navigation,
        "JavaScripts" => $javaScripts,
        "StyleSheets" => [
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
        ],
    ];
    $content = $adminPage->Load("config_list.html", $header);

    $configList = new ConfigList();
    $configList->LoadConfigList($request->GetProperty("Section"));
    $content->LoadFromObjectList("ConfigList", $configList);

    if ($request->GetProperty("Do") == "RemoveImportedData" && $request->GetProperty("Template")) {
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($request->GetIntProperty("company_unit_id"));
        $companyUnit->RemoveCompanyUnitData();
        $content->LoadMessagesFromObject($companyUnit);

        $link = "config.php";
        Operation::Save($link, "config", "config_ou_remove", $request->GetProperty("company_unit_id"));
    }

    if ($request->GetProperty("Do") == "PartnerTypeParams" && $request->GetProperty("Template")) {
        $partnerType = new PartnerType($request->GetProperties());
        $partnerType->Save();
        $content->LoadMessagesFromObject($partnerType);
        $content->LoadErrorsFromObject($partnerType);
        $link = "config.php";
        $partnerType->GetTypeByAbbr($request->GetProperty("partner_type"));
        Operation::Save($link, "config", "config_partner_type_edit", $partnerType->GetProperty("partner_type_id"));
    }

    if ($request->GetProperty("Do") == "SaveOptionValues" && $request->GetProperty("Template")) {
        $options = $request->GetProperty("option_id");
        $optionsUpdated = 0;
        $messageObject = new LocalObject();
        for ($i = 0; $i < count($options); $i++) {
            if (!$options[$i]) {
                continue;
            }
            foreach ($request->GetProperty("company_unit_id") as $companyUnitId) {
                $option = new Option("product");
                if (
                    $option->SaveOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $options[$i],
                        $request->GetProperty("option_value")[$i],
                        $companyUnitId
                    )
                ) {
                    $optionsUpdated++;
                } else {
                    $messageObject->_errors = array_merge($messageObject->_errors, $option->GetErrors());
                }
            }
        }
        $messageObject->AddMessage("config-message_records_updated", null, ["number" => $optionsUpdated]);
        $content->LoadErrorsFromObject($messageObject);
        $content->LoadMessagesFromObject($messageObject);
        $link = "config.php";
        Operation::Save($link, "config", "config_save_option_values");
    }

    //Company list
    $companyList = new CompanyUnitList($module);
    $companyList->LoadCompanyUnitListForTree();
    $companyListHtml = "";
    foreach ($companyList->GetItems() as $item) {
        $companyListHtml .= "<option value=" . Connection::GetSQLString($item["company_unit_id"]) . "
            data-title=" . Connection::GetSQLString($item["title"]) . ">
                " . $item["select_prefix"] . $item["title"] . ",
                " . GetTranslation("remove-company-unit-id") . " " . $item["company_unit_id"] . "
            </option>";
    }
    $content->SetVar("CompanyListHtml", $companyListHtml);

    $user = new User();
    $user->LoadBySession();
    $permissionList = $user->GetProperty("PermissionList");
    foreach ($permissionList as $permission) {
        if ($permission["name"] == "root") {
            $content->SetVar("CloudAdmin", true);
            break;
        }
    }

    //Options list
    $productList = [];
    $productGroupList = new ProductGroupList("product");
    $productGroupList->LoadProductGroupListForAdmin();
    foreach ($productGroupList->_items as $productGroup) {
        $productListObject = new ProductList("product");
        $productListObject->LoadProductListForAdmin($productGroup["group_id"]);
        foreach ($productListObject->_items as $product) {
            $optionList = new OptionList("product");
            $optionList->LoadOptionListForAdmin($product["product_id"], OPTION_LEVEL_COMPANY_UNIT);
            $product["OptionList"] = $optionList->GetItems();
            $product["OptionList"][] = [
                "option_id" => "start_date",
                "title_translation" => GetTranslation("start-date"),
                "type" => "date",
                "product_id" => $product["product_id"],
            ];
            $product["OptionList"][] = [
                "option_id" => "end_date",
                "title_translation" => GetTranslation("end-date"),
                "type" => "date",
                "product_id" => $product["product_id"],
            ];
            $productList[] = $product;
        }
    }
    $content->SetLoop("ProductList", $productList);

    //Partner type list
    $partnerTypeList = new PartnerTypeList();
    $partnerTypeList->LoadPartnerTypeList();
    $content->LoadFromObjectList("PartnerTypeList", $partnerTypeList);

    if ($request->GetProperty("Do") == "ExportEmail") {
        if ($request->GetProperty("WhoToSendPush") == "employee") {
            EmployeeList::ExportEmailList(
                $request->GetProperty("employee_id"),
                $request->GetProperty("version"),
                $request->GetProperty("company_unit_id"),
                $request->GetProperty("version_operation")
            );
        } else {
            ContactList::ExportEmailList(
                $request->GetProperty("contact_id"),
                $request->GetProperty("company_unit_id"),
                $request->GetProperty("contact_type"),
                $request->GetProperty("contact_for")
            );
        }
    }

    if ($request->GetProperty("Do") == "SendPushForAll") {
        $text = $request->GetProperty("push_for_all_text");
        if ($text) {
            EmployeeList::SendMessageForEmployees($text);
            $configList->AddMessage("push-sended", null, ["text" => $text]);
            $content->LoadMessagesFromObject($configList);
        } else {
            $configList->AddError("push-not-sent-no-text");
            $content->LoadErrorsFromObject($configList);
        }
    }

    //Replacements list
    $employee = new Employee("company");
    $contact = new Contact("company");
    $companyUnit = new CompanyUnit("company");

    $replacements = $employee->GetReplacementsList();
    $replacements = $replacements["ReplacementList"];
    $content->SetLoop("EmployeeReplacements", $replacements);

    $replacements = $contact->GetReplacementsList();
    $replacements = $replacements["ReplacementList"];
    $content->SetLoop("ContactReplacements", $replacements);

    $replacements = $companyUnit->GetReplacementsList();
    $replacements = $replacements["ReplacementList"];
    $content->SetLoop("CompanyUnitReplacements", $replacements);

    $productGroupReplacements = [];
    $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
    $replacementsTmp = $specificProductGroup->GetReplacementsList(false, "", false);
    $productGroupReplacements = array_merge($productGroupReplacements, $replacementsTmp["ReplacementList"]);
    $content->SetLoop("ProductGroupReplacements", $productGroupReplacements);

    //send either email or push notification to selected employees or contacts of company units
    if ($request->GetProperty("Do") == "SendPushForFew") {
        if ($request->GetProperty("push_for_few_text") || $request->GetProperty("email_for_few_text")) {
            $selectedEmployeeIDs = [];
            $tmpVersionList = $request->GetProperty("version");
            $versionList = [];

            if ($request->IsPropertySet("employee_id") && count($request->GetProperty("employee_id")) > 0) {
                $selectedEmployeeIDs = $request->GetProperty("employee_id");
            } else {
                $selectedEmployeeIDs = EmployeeList::GetEmployeeListByVersionList(
                    $tmpVersionList,
                    $request->GetProperty("company_unit_id"),
                    $request->GetProperty("version_operation")
                );
                $selectedEmployeeIDs = array_column($selectedEmployeeIDs["EmployeeList"], "employee_id");
            }

            //prepare version list for further device filter
            if (is_array($tmpVersionList) && count($tmpVersionList) > 0) {
                if ($request->GetProperty("version_operation") !== "=") {
                    $versionList = DeviceList::GetExtendedDeviceListByVersion(
                        $tmpVersionList,
                        $request->GetProperty("version_operation")
                    );
                } else {
                    foreach ($tmpVersionList as $version) {
                        $version = explode("-", $version);
                        $versionList[] = ["client" => $version[0], "version" => $version[1]];
                    }
                }
            }

            if ($request->GetProperty("WhatToSend") == "push") {
                $text = $request->GetProperty("push_for_few_text");

                if ($request->GetProperty("WhoToSendPush") == "employee") {
                    EmployeeList::SendMessageForEmployees($text, $selectedEmployeeIDs, true, $versionList);
                } else {
                    ContactList::SendMessageForContacts(
                        $text,
                        $request->GetProperty("contact_id"),
                        true,
                        $request->GetProperty("company_unit_id"),
                        $request->GetProperty("contact_type"),
                        $request->GetProperty("contact_for")
                    );
                }

                $configList->AddMessage("push-sended", null, ["text" => $text]);
                $content->LoadMessagesFromObject($configList);
            } else {
                $text = $request->GetProperty("email_for_few_text");
                $subject = $request->GetProperty("email_subject");

                if ($request->GetProperty("WhoToSendPush") == "employee") {
                    EmployeeList::SendMessageForEmployees($text, $selectedEmployeeIDs, false, $versionList, $subject);
                } else {
                    ContactList::SendMessageForContacts(
                        $text,
                        $request->GetProperty("contact_id"),
                        false,
                        $request->GetProperty("company_unit_id"),
                        $request->GetProperty("contact_type"),
                        $request->GetProperty("contact_for"),
                        $subject
                    );
                }

                $configList->AddMessage("email-sended", null, ["text" => $text]);
                $content->LoadMessagesFromObject($configList);
            }
        } else {
            $configList->AddError("push-not-sent-no-text");
            $content->LoadErrorsFromObject($configList);
        }
    }

    $content->SetVar("ReturnPath", urlencode(ADMIN_PATH . "config.php?Section=" . $request->GetProperty("Section")));
}

$content->SetVar("Section", $request->GetProperty("Section"));
$content->SetLoop("SectionList", $templateSectionList);
$adminPage->Output($content);
