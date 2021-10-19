<?php
$user = new User();

$billableItem = new BillableItem;

$billId = $request->GetProperty('billable_item_id');
$companyId = $request->GetProperty('company_unit_id');

$selectValue = $billableItem->getBillalbleReasons();

$urlFilter->AppendFromObject($request);

if ($billId == 0) {


    $billableList = new BillableItemList('company');


    $navigation[] = array(
        "Title" => GetTranslation("add-new-bill", "company"),
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
    );

    $header = array(
        "Title" => GetTranslation("add-new-bill"),
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
        )
    );

    $billing = new BillableItemList('company');

    $content = $adminPage->Load('billable_item_edit.html', $header);
    $content->SetLoop("selectValue", $selectValue);
    $content->SetVar("billId", $billId);

    // get userId
    $session = &GetSession();
    $content->SetVar('userId', $session->GetProperty('LoggedInUser')['user_id']);

    $content->SetVar('companyId', $companyId);

    $user->LoadBySession();
    if ($user->Validate(["company_unit"])) {
        $content->SetVar("Admin", "Y");
    }
    $content->SetVar('ParamForURL', $urlFilter->GetForURL(["Section"]) . "&ActiveTab=7");
} else {

    $navigation[] = array(
        "Title" => GetTranslation("edit-bill", "company"),
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
    );

    $header = array(
        "Title" => GetTranslation("Edit bill", $module),
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
        )
    );


    $content = $adminPage->Load('billable_item_edit.html', $header);
    $content->SetVar("billId", $billId);
    $content->SetVar('companyId', $companyId);

    // bill data
    $billData = $billableItem->getBillData($request->GetProperty('billable_item_id'));
    $content->SetVar("quantity", $billData['quantity']);
    $content->SetVar("discount", $billData['discount']);
    $content->SetVar("dateStart", $billData['date_start']);
    $content->SetVar("dateEnd", $billData['date_end']);
    $content->SetVar("created", $billData['created']);
    $content->SetVar("inInvoice", $billData['invoice_id'] > 0);
    $content->SetVar("itemName", $billData['item_name']);
    $content->SetVar("amount", $billData['price']);

    $selectValue = array_values($selectValue);
    $content->setLoop('selectValue', $selectValue);

    if ($billableItem->checkBillById($billId, $companyId)) {
        $user->LoadBySession();
        if ($user->Validate(["company_unit"])) {
            $content->SetVar("Admin", "Y");
        }
    }

    $content->SetVar('ParamForURL', $urlFilter->GetForURL(["Section"]) . "&ActiveTab=7");
}