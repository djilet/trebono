<?php

$user->ValidateAccess(array("root", "partner" => null), "or");

$navigation[] = array(
    "Title" => GetTranslation("module-title", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("FilterTitle", "ItemsOnPage");

if ($request->IsPropertySet("contact_id")) {
    $urlFilter->AppendFromObject($request, array_merge(array('Page', 'partner_id'), $filterParams));

    $title = $request->GetProperty("contact_id") > 0 ? GetTranslation("title-contact-edit", $module) : GetTranslation("title-contact-add", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $request->GetProperty("contact_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/inputmask/jquery.inputmask.bundle.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-validation/js/jquery.validate.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/select2/select2.min.js")
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/select2/select2.css")
        )
    );

    $content = $adminPage->Load("contact_edit.html", $header);

    if ($request->GetProperty("contact_id")) {
        $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $request->GetProperty("contact_id");
        Operation::Save($link, "partner", "contact_id", $request->GetProperty("contact_id"));
    }

    $contact = new PartnerContact($module);

    if ($request->GetProperty("Save")) {
        foreach ($request->GetProperty("contact_for") as $cf) {
            $request->SetProperty("contact_for_" . $cf, "Y");
        }

        $contact->LoadFromObject($request);
        if ($contact->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $contact->GetProperty("contact_id");
            Operation::Save($link, "company", "contact_id_save", $contact->GetProperty("contact_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            $content->LoadErrorsFromObject($contact);
        }
    } else {
        $contact->LoadByID($request->GetProperty("contact_id"));
    }
    $content->LoadFromObject($contact);

    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    $employeeList = new EmployeeList($module);
    $employeeList->LoadEmployeeList($request);
    $content->SetLoop("EmployeeList", $employeeList->GetItems());
} elseif ($request->IsPropertySet("partner_id")) {
    $urlFilter->AppendFromObject($request, array_merge(array('Page'), $filterParams));

    $title = $request->GetProperty("partner_id") > 0 ? GetTranslation("title-partner-edit", $module) : GetTranslation("title-partner-add", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&partner_id=" . $request->GetProperty("partner_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/inputmask/jquery.inputmask.bundle.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-validation/js/jquery.validate.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js")
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
        )
    );

    $content = $adminPage->Load("partner_edit.html", $header);
    $content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));

    if ($request->GetProperty("partner_id")) {
        $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&partner_id=" . $request->GetProperty("partner_id");
        Operation::Save($link, "partner", "partner_id", $request->GetProperty("partner_id"));
    }

    if ($request->GetProperty("Action") == "CompanyMultiSelect") {
        if ($request->GetProperty("Delete")) {
            $productList = array_filter(array_values($request->GetProperty("product_id")));
            $contractList = new PartnerContractList($module);
            $contractList->LoadContractListByCompany(
                $request->GetProperty("partner_id"),
                $request->GetProperty("company_unit_id")
            );
            $removedContracts = 0;
            foreach ($contractList->GetItems() as $contract) {
                if (!in_array($contract['product_id'], $productList)) {
                    continue;
                }
                $contract2remove = new PartnerContract($module);
                $contract2remove->LoadByContractID($contract['partner_contract_id']);
                $contract2remove->CloseContract($request->GetProperty("end_date"));
                if ($contract2remove->HasErrors()) {
                    $content->LoadErrorsFromObject($contract2remove);
                    break;
                }
                $removedContracts++;
            }
            $messageObject = new CommonObject();
            $messageObject->AddMessage("contracts-removed", "partner", ['count' => $removedContracts]);
            $content->LoadMessagesFromObject($messageObject);
        } else {
            $productList = array_filter(array_values($request->GetProperty("product_id")));
            $newContracts = 0;
            foreach ($productList as $product) {
                $request->RemoveProperty("product_id");
                $request->SetProperty("product_id", $product);
                $contract = new PartnerContract($module);
                $contract->Create($request);
                if ($contract->HasErrors()) {
                    $content->LoadErrorsFromObject($contract);
                    break;
                }
                $newContracts++;
            }
            $messageObject = new CommonObject();
            if ($productList) {
                $messageObject->AddMessage("contracts-created", "partner", ['count' => $newContracts]);
                $content->LoadMessagesFromObject($messageObject);
            } else {
                $messageObject->AddError("product-list-empty", "partner");
                $content->LoadErrorsFromObject($messageObject);
            }
            Operation::Save($link, "partner", "contact_create");
        }
    }
    if ($request->GetProperty("Do") == "RemoveContact" && $request->GetProperty("ContactIDs")) {
        $contactList = new ContactList($module);
        $contactList->Remove($request->GetProperty("ContactIDs"));
        $content->LoadMessagesFromObject($contactList);
        $content->LoadErrorsFromObject($contactList);
        Operation::Save($link, "partner", "contact_delete");
    }
    if ($request->GetProperty("Do") == "Action" && $request->GetProperty("Action") == "RemoveContract") {
        $contract = new PartnerContract($module);
        $contract->LoadByContractID($request->GetProperty("partner_contract_id"));
        $contract->CloseContract();
        $content->LoadMessagesFromObject($contract);
        Operation::Save($link, "partner", "contract_delete");
    }
    if ($request->GetProperty("Do") == "Action" && $request->GetProperty("Action") == "ExportCommission") {
        $report = new Report();
        $report->LoadByPartner($request->GetProperty("partner_id"), $request->GetProperty("ExportDate"));
        $report->SaveCommissionLines();

        if ($report_file = $report->Export()) {
            $content->SetVar("ExportCommissionFile", $report_file);
        } else {
            $content->LoadMessagesFromObject($report);
        }

        if ($report->HasErrors()) {
            $content->LoadErrorsFromObject($report);
        }

        Operation::Save($link, "partner", "export_commission");
    }

    $partner = new Partner($module);

    if ($request->GetProperty("Save")) {
        $partner->LoadFromObject($request);
        $partner->SetProperty("Link", $link);
        if ($partner->Save()) {
            if (isAjax()) {
                echo json_encode([
                    "result" => "success",
                    "partner_id" => $partner->GetProperty("partner_id"),
                ]);
                exit(0);
            }

            //header("Location: ".$moduleURL."&partner_id=".$partner->GetPropertyForURL("partner_id"));
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&partner_id=" . $partner->GetProperty("partner_id");
            Operation::Save($link, "partner", "partner_id_save", $partner->GetProperty("partner_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            if (isAjax()) {
                echo json_encode([
                    "result" => "error",
                ]);
                exit(0);
            }

            $content->LoadErrorsFromObject($partner);
        }
    } else {
        $partner->LoadByID($request->GetProperty("partner_id"));
    }
    $user->LoadBySession();
    $request->SetProperty("user_id", $user->GetProperty("user_id"));
    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null, "partner" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    $content->LoadFromObject($partner);

    //Contact list
    $contactList = new PartnerContactList($module);
    $contactList->LoadContactList($partner->GetProperty("partner_id"));
    $content->LoadFromObjectList("ContactList", $contactList);

    //Commission list
    if ($partner->GetProperty("partner_id")) {
        $commissionList = new CommissionList($module);
        $commissionList->SetOrderBy("type_desc");
        $commissionList->LoadCommissionList($request);
        $content->LoadFromObjectList("CommissionList", $commissionList);
        $content->SetVar(
            "CommissionPaging",
            $commissionList->GetPagingAsHTML($moduleURL . "&" . $urlFilter->GetForURL(array("Page")) . "&partner_id=" . $partner->GetProperty("partner_id"))
        );
    }

    /******Product settings********/
    $moduleProduct = "product";
    $groupList = new ProductGroupList($moduleProduct);
    $groupList->LoadProductGroupListForAdmin();
    for ($i = 0; $i < $groupList->GetCountItems(); $i++) {
        $productList = new ProductList($moduleProduct);
        $productList->LoadProductListForAdmin($groupList->_items[$i]["group_id"]);
        for ($j = 0; $j < $productList->GetCountItems(); $j++) {
            $contractList = new PartnerContractList($module);
            $contractList->LoadContractListByProductID(
                $partner->GetProperty("partner_id"),
                $productList->_items[$j]["product_id"]
            );
            $productList->_items[$j]["ContractList"] = $contractList->GetItems();
            $productList->_items[$j]["CountContracts"] = $contractList->GetCountItems();
        }
        $groupList->_items[$i]["ProductList"] = $productList->GetItems();
    }
    $content->LoadFromObjectList("ProductGroupList", $groupList);
    //Company list
    $companyList = new CompanyUnitList($module);
    $companyList->LoadCompanyUnitListForTree(null, "company_unit", "N");
    $companyListHtml = "";
    foreach ($companyList->GetItems() as $item) {
        $companyListHtml .= "<option value='" . $item['company_unit_id'] . "' data-title='" . $item['title'] . "'>" . $item['select_prefix'] . $item['title'] . "</option>";
    }
    $content->SetVar("CompanyListHtml", $companyListHtml);

    $request->SetProperty("FilterPartner", $partner->GetProperty("partner_id"));
    $companyList = new CompanyUnitList($module);
    $companyList->LoadCompanyUnitLinearList($request, true);
    $companyListHtml = "";
    foreach ($companyList->GetItems() as $item) {
        $companyListHtml .= "<option value='" . $item['company_unit_id'] . "' data-title='" . $item['title'] . "'>" . $item['title'] . "</option>";
    }
    $content->SetVar("CurrentCompanyListHtml", $companyListHtml);

    //Partner type list
    $partnerTypeList = new PartnerTypeList();
    $partnerTypeList->LoadPartnerTypeList();
    $content->LoadFromObjectList("PartnerTypeList", $partnerTypeList);
    $content->SetVar(
        "ExportDate",
        $request->IsPropertySet("ExportDate") ? $request->GetProperty("ExportDate") : date("Y-m-d")
    );
} else {
    $urlFilter->AppendFromObject($request, $filterParams);

    $header = array(
        "Title" => GetTranslation("module-title", $module),
        "Navigation" => $navigation,
        "JavaScripts" => array(array("JavaScriptFile" => ADMIN_PATH . "template/plugins/tree-table/javascript.js"))
    );

    $content = $adminPage->Load("partner_list.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "partner", "partner_list");

    if ($request->GetProperty('Do') == 'RemovePartner' && $request->GetProperty("PartnerIDs")) {
        $partnerList = new PartnerList($module);
        $partnerList->Remove($request->GetProperty("PartnerIDs"));
        $content->LoadMessagesFromObject($partnerList);
        $content->LoadErrorsFromObject($partnerList);
        Operation::Save($link, "partner", "partner_delete");
    } else {
        if ($request->GetProperty('Do') == 'ActivatePartner' && $request->GetProperty("PartnerIDs")) {
            $partnerList = new PartnerList($module);
            $partnerList->Activate($request->GetProperty("PartnerIDs"));
            $content->LoadMessagesFromObject($partnerList);
            $content->LoadErrorsFromObject($partnerList);
            Operation::Save($link, "partner", "partner_activate");
        }
    }

    //load filter data from session and to session
    $session = GetSession();
    foreach ($filterParams as $key) {
        if ($session->IsPropertySet("Partner" . $key) && !$request->IsPropertySet($key)) {
            $request->SetProperty($key, $session->GetProperty("Partner" . $key));
        } else {
            if ($request->IsPropertySet($key)) {
                $request->SetProperty($key, urldecode($request->GetProperty($key)));
            }
            $session->SetProperty("Partner" . $key, $request->GetProperty($key));
        }
    }
    $session->SaveToDB();

    $partnerList = new PartnerList($module);
    $partnerList->LoadPartnerList($request);

    for ($j = 0; $j < $partnerList->GetCountItems(); $j++) {
        $contactList = new PartnerContactList($module);
        $contactList->LoadContactList($partnerList->_items[$j]["partner_id"]);
        $partnerList->_items[$j]["ContactList"] = $contactList->GetItems();
    }

    $content->LoadFromObjectList("PartnerList", $partnerList);

    $content->SetVar("Paging", $partnerList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
    $content->SetVar("ListInfo", GetTranslation(
        'list-info1',
        array('Page' => $partnerList->GetItemsRange(), 'Total' => $partnerList->GetCountTotalItems())
    ));
    $content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
    $content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage")));
    $content->LoadFromObject($request, $filterParams);
    if (!$request->IsPropertySet("FilterArchive")) {
        $content->SetVar("FilterArchive", "N");
    }
    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    $itemsOnPageList = array();
    foreach (array(10, 20, 50, 100, 0) as $v) {
        $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $partnerList->GetItemsOnPage() ? 1 : 0);
    }
    $content->SetLoop("ItemsOnPageList", $itemsOnPageList);
}
