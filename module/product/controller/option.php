<?php
$user->ValidateAccess(array("root"));

$navigation[] = array(
    "Title" => GetTranslation("section-option", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$header = array(
    "Title" => GetTranslation("section-option", $module),
    "Navigation" => $navigation,
    "JavaScripts" => array(
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
    ),
    "StyleSheets" => array(
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
    )
);

$content = $adminPage->Load("option_list.html", $header);

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "product", "option_list");

if ($request->GetProperty("Save")) {
    $messageObject = new CommonObject();
    Option::AutomaticAdoption($request);
    foreach ($request->GetProperty('Product') as $productID => $product) {
        $optionList = new OptionList($module);
        $optionList->LoadOptionListForAdmin($productID, OPTION_LEVEL_GLOBAL);

        $created = null;
        if (isset($product['date_of_params']) && $product['date_of_params']) {
            $created = $product['date_of_params'];
        }

        foreach ($optionList->GetItems() as $item) {
            $optionValue = '';
            if (isset($product["Option"][$item["option_id"]])) {
                $optionValue = $product["Option"][$item["option_id"]];
            }

            $option = new Option($module);
            $result = $option->SaveOptionValue(
                OPTION_LEVEL_GLOBAL,
                $item["option_id"],
                $optionValue ?? null,
                null,
                $created
            );
            if (!$result) {
                $messageObject->AppendErrorsFromObject($option);
            }
        }

        $content->LoadErrorsFromObject($messageObject);
    }
    Operation::Save($link, "product", "option_save");
    if (isAjax()) {
        echo json_encode([
            'result' => $messageObject->HasErrors() ? 'error' : 'success'
        ]);
        exit(0);
    }
}
if ($user->Validate(array("root"))) {
    $content->SetVar("Admin", 'Y');
}
if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
    $content->SetVar("HistoryAdmin", 'Y');
}
$content->SetVar("DateOfParams", date("Y-m-d"));

$groupList = new ProductGroupList($module);
$groupList->LoadProductGroupListForAdmin();

$typesOptionsSelect = array(
    'monthly-yearly-select' => array(OPTION__BENEFIT__MAIN__RECEIPT_OPTION, OPTION__AD__MAIN__RECEIPT_OPTION),
    'monthly-quarterly-yearly-select' => array(OPTION__STORED_DATA__MAIN__FREQUENCY),
    "custom-select" => array_merge(
        array_values(OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO),
        array_values(OPTIONS_VOUCHER_DEFAULT_REASON)
    )
);

$typesOptionsTextarea = array(
    'textarea' => array(
        OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
        OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
        OPTION__MOBILE__MAIN__MOBILE_MODEL,
        OPTION__MOBILE__MAIN__MOBILE_NUMBER
    )
);

for ($i = 0; $i < $groupList->GetCountItems(); $i++) {
    $productList = new ProductList($module);
    $productList->LoadProductListForAdmin($groupList->_items[$i]["group_id"]);
    for ($j = 0; $j < $productList->GetCountItems(); $j++) {
        $optionList = new OptionList($module);
        $optionList->LoadOptionListForAdmin($productList->_items[$j]["product_id"], OPTION_LEVEL_GLOBAL);
        for ($k = 0; $k < $optionList->GetCountItems(); $k++) {
            $optionList->_items[$k]["value"] = Option::GetCurrentValue(OPTION_LEVEL_GLOBAL,
                $optionList->_items[$k]["option_id"], null);

            foreach ($typesOptionsSelect as $key => $value) {
                if (in_array($optionList->_items[$k]["code"], $value)) {
                    $optionList->_items[$k]["type"] = $key;
                }
            }

            foreach ($typesOptionsTextarea as $key => $value) {
                if (in_array($optionList->_items[$k]["code"], $value)) {
                    $optionList->_items[$k]["type"] = $key;
                }
            }

            if ($optionList->_items[$k]["title"] == "Salary option") {
                $optionList->_items[$k]["type"] = "salary-select";
            }

            if (in_array($optionList->_items[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO)) {
                $voucherScenario = $optionList->_items[$k]["value"] ?? $optionList->_items[$k]["inherited_value"];
                $optionList->_items[$k]["SelectList"] = Option::GetVoucherReasonScenarioList($voucherScenario);
            }

            if (in_array($optionList->_items[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON)) {
                $optionList->_items[$k]["SelectList"] = Voucher::GetVoucherReasonList(
                    $optionList->_items[$k]["value"],
                    "voucher_sets_of_goods"
                );
            }
        }
        $productList->_items[$j]["OptionList"] = $optionList->GetItems();
    }
    $groupList->_items[$i]["ProductList"] = $productList->GetItems();
}

$content->LoadFromObjectList("ProductGroupList", $groupList);
