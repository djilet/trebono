<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

$module = "partner";
$moduleURL = "module.php?load=" . $module;

$result = array();

$user = new User();
if (!$user->LoadBySession() || !$user->Validate(array("root", "partner" => null), "or")) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));

    switch ($request->GetProperty("Action")) {
        case "GetContractHistoryPartnerHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_contract_history.html");

            $productCode = Product::GetProductCodeByID($request->GetProperty("product_id"));
            $content->SetVar("PropertyNameTranslation", GetTranslation("product-" . $productCode, "product"));

            $contractList = new PartnerContractList($module);
            $contractList->LoadContractListByProductID(
                $request->GetProperty("partner_id"),
                $request->GetProperty("product_id"),
                true
            );
            $content->LoadFromObjectList("ContractList", $contractList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetPropertyHistoryContactHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = PartnerContact::GetPropertyValueListContact(
                $request->GetProperty("property_name"),
                $request->GetProperty("contact_id")
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("ShowValue", "Y");

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetPropertyHistoryPartnerHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = Partner::GetPropertyValueListPartner(
                $request->GetProperty("property_name"),
                $request->GetProperty("partner_id")
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("ShowValue", "Y");

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "SearchUser":
            if (!$q = $request->GetProperty("q")) {
                break;
            }
            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT user_id AS id, " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " AS name,
                        	email, " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("first_name") . " AS last_name, phone, salutation
                      	FROM user_info 
						WHERE " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " ILIKE '%" . $q . "%' AND archive='N'";
            $result = $stmt->FetchList($query);
            break;
        case "SearchCompany":
            if (!$q = $request->GetProperty("q")) {
                break;
            }
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT company_unit_id AS id,
                        phone, email, zip_code, country, city, house, 
						comment, bank_details, bic, register,
                        tax_number, tax_consultant,
                        " . Connection::GetSQLDecryption("title") . " AS title,
                        " . Connection::GetSQLDecryption("iban") . " AS iban,
                        " . Connection::GetSQLDecryption("street") . " AS street
                      FROM company_unit WHERE " . Connection::GetSQLDecryption("title") . " ILIKE '%" . $q . "%' AND archive='N'";
            $result = $stmt->FetchList($query);
            break;
        case "CreatePartnerContract":
            $contract = new PartnerContract($module);
            $contract->Create($request);
            if ($contract->HasErrors()) {
                $result['Error'] = $contract->GetErrorsAsString();
                $result['ok'] = false;
            } else {
                $result = $contract->GetProperties();
                $result['ok'] = true;
            }
            break;
        case "PartnerContractValidate":
            $contract = new PartnerContract($module);
            $contract->AppendFromObject($request);
            $productIDs = $request->GetProperty('product_ids');

            $intersectionList = [];
            foreach ($productIDs as $productID) {
                $contract->SetProperty('product_id', $productID);
                if (!$partnerID = $contract->Validate()) {
                    continue;
                }

                $product = new Product("product");
                $product->LoadByID($productID);
                $intersectionList[] = GetTranslation(
                    "contract-intersection-found",
                    $module,
                    [
                        'product' => $product->GetProperty("title_translation"),
                        'company_unit' => CompanyUnit::GetTitleByID($contract->GetProperty('company_unit_id')),
                        'partner' => Partner::GetTitleByID($partnerID),
                    ]
                );
            }
            $result = $intersectionList;
            break;
    }
}


echo json_encode($result);
