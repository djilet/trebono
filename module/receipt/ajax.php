<?php

define("IS_ADMIN", true);

require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

$module = "receipt";
$moduleURL = "module.php?load=" . $module;

$result = array();

$user = new User();
if (!$user->LoadBySession() || !$user->ValidateAccess(array("receipt" => null, "tax_auditor" => null), "or")) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
} else {
    $request = new LocalObject(array_merge($_GET, $_POST));

    switch ($request->GetProperty("Action")) {
        case "GetPropertyHistoryReceiptHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $optionCodes = array(
                OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
                OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
                OPTION__MOBILE__MAIN__MOBILE_MODEL,
                OPTION__MOBILE__MAIN__MOBILE_NUMBER,
                OPTION__FOOD__MAIN__IMPORTANT_INFO,
                OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO
            );
            $optionCodes = array_merge($optionCodes, array_values(OPTIONS_INTERNAL_VERIFICATION_INFO));

            $optionCode = Option::GetCodeByOptionID($request->GetProperty("property_name"));

            if (in_array($optionCode, $optionCodes)) {
                $valueList = Option::GetOptionValueList(
                    OPTION_LEVEL_EMPLOYEE,
                    $request->GetProperty("property_name"),
                    $request->GetProperty("employee_id")
                );
                $popupPage = new PopupPage("product", true);
                $content = $popupPage->Load("block_option_value_history.html");

                $optionTitleTranslation = GetTranslation("option-" . $optionCode, "product");
                $content->SetVar("option_title_translation", $optionTitleTranslation);
            } elseif ($request->GetProperty("property_name") == "material_status" || $request->GetProperty("property_name") == "child_count") {
                $valueList = Employee::GetPropertyValueListEmployee(
                    $request->GetProperty("property_name"),
                    $request->GetProperty("employee_id")
                );
            } else {
                $valueList = Receipt::GetPropertyValueListReceipt(
                    $request->GetProperty("property_name"),
                    $request->GetProperty("receipt_id")
                );
            }

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetReceiptFileSignatureLogHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_signature_log.html");

            $log = ReceiptFile::GetLog($request->GetProperty("receipt_file_id"));
            $content->SetVar("receipt_file_id", $request->GetProperty("receipt_file_id"));
            $content->LoadFromArray($log);

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetReceiptListHTML":
            $filterParams = array(
                "FilterLegalReceiptID",
                "FilterCreditorNumber",
                "FilterVoucherID",
                "FilterCreatedRange",
                "FilterStatus",
                "UserReceiptConfirm",
                "FilterName",
                "FilterCompanyTitle",
                "FilterProductGroup",
                "FilterArchive",
                "FilterTripID",
                "FilterNotBooked",
                "FilterHasUnreadMessagesAdmin",
                "FilterHasUnreadMessagesEmployee",
                "FilterHasChat",
                "FilterAutomaticProcessed",
                "ItemsOnPage",
                "FilterUserLastChangedStatus",
            );

            //load filter data from session and to session
            $session = GetSession();
            foreach ($filterParams as $key) {
                if (!$session->IsPropertySet("Receipt" . $key)) {
                    continue;
                }

                $request->SetProperty($key, $session->GetProperty("Receipt" . $key));
            }
            $session->SaveToDB();

            if (strlen($request->GetProperty("FilterCreatedRange")) == 0) {
                $request->SetProperty("FilterCreatedRange", date("01/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
            }

            $urlFilter = new URLFilter();
            $urlFilter->LoadFromObject($request, array("Section", "Page"));
            //$urlFilter->AppendFromObject($request, $filterParams);

            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_receipt_list.html");

            $permission = "receipt";
            if ($user->Validate(array("tax_auditor" => null))) {
                $permission = "tax_auditor";
                $content->SetVar("tax_auditor", 1);
            }

            $receiptList = new ReceiptList($module);
            $receiptList->SetOrderBy("admin");
            $receiptList->LoadReceiptListForAdmin($request, false, $permission);
            $content->LoadFromObjectList("ReceiptList", $receiptList);

            $content->SetVar(
                "Paging",
                $receiptList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL(array("Page")))
            );
            $content->SetVar("ListInfo", GetTranslation(
                'list-info1',
                array('Page' => $receiptList->GetItemsRange(), 'Total' => $receiptList->GetCountTotalItems())
            ));

            $itemsOnPageList = array();
            foreach (array(10, 20, 50, 100, 0) as $v) {
                $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $receiptList->GetItemsOnPage() ? 1 : 0);
            }
            $content->SetLoop("ItemsOnPageList", $itemsOnPageList);

            $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
            $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
            $content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage", "Page")));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetReceiptLineListHTML":
            $urlFilter = new URLFilter();
            $urlFilter->LoadFromObject($request, array("Section", "receipt_id"));

            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_receipt_line_list.html");
            $receiptLineList = new ReceiptLineList($module);
            $receiptLineList->LoadLineList($request->GetProperty("receipt_id"));

            $receipt = new Receipt($module);
            $receipt->LoadByID($request->GetProperty("receipt_id"));

            if ($receipt->GetProperty("status") == "approved" || $receipt->GetProperty("status") == "denied") {
                for ($i = 0; $i < $receiptLineList->GetCountItems(); $i++) {
                    $receiptLineList->_items[$i]["close"] = true;
                }
            }
            $content->LoadFromObjectList("ReceiptLineList", $receiptLineList);

            $cost = 0;
            $costApproved = 0;

            $countProduct = array();

            for ($i = 0; $i < $receiptLineList->GetCountItems(); $i++) {
                $cost += $receiptLineList->_items[$i]["price"] * $receiptLineList->_items[$i]["quantity"];

                if ($receiptLineList->_items[$i]["approved"] != 'Y') {
                    continue;
                }

                if ($receipt->GetProperty("receipt_from") == "restaurant") {
                    if (!array_key_exists($receiptLineList->_items[$i]["title"], $countProduct)) {
                        $countProduct[$receiptLineList->_items[$i]["title"]] = $receiptLineList->_items[$i]["quantity"];
                    } else {
                        $countProduct[$receiptLineList->_items[$i]["title"]] += $receiptLineList->_items[$i]["quantity"];
                    }

                    if ($receiptLineList->_items[$i]["quantity"] > 2) {
                        $costApproved += $receiptLineList->_items[$i]["price"] * 2;
                    } elseif ($countProduct[$receiptLineList->_items[$i]["title"]] > 2) {
                        continue;
                    } else {
                        $costApproved += $receiptLineList->_items[$i]["price"] * $receiptLineList->_items[$i]["quantity"];
                    }
                } else {
                    $costApproved += $receiptLineList->_items[$i]["price"] * $receiptLineList->_items[$i]["quantity"];
                }
            }
            $cost = round($cost, 2);
            $content->SetVar("cost", $cost);

            $costApproved = round($costApproved, 2);
            $content->SetVar("cost_approved", $costApproved);

            $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
            $content->SetVar("ReceiptFrom", $receipt->GetProperty("receipt_from"));
            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "SaveReceiptLine":
            $receiptLine = new ReceiptLine($module);
            $receiptLine->LoadFromObject($request);
            if ($receiptLine->Save()) {
                $result["Status"] = "success";
                $link = $moduleURL . "&Section=receipt&receipt_id=" . $request->GetProperty("receipt_id");
                Operation::Save($link, "receipt", "receipt_line_add", $request->GetProperty("receipt_id"));
            } else {
                $result["Status"] = "error";
                $result["Error"] = $receiptLine->GetErrorsAsString();
            }
            break;
        case "RemoveReceiptLine":
            $receiptLineList = new ReceiptLineList($module);
            $receiptLineList->Remove($request->GetProperty("LineIDs"));
            break;
        case "ApproveReceiptLine":
            $receiptLineList = new ReceiptLineList($module);
            $receiptLineList->Approve($request->GetProperty("LineIDs"), $request->GetProperty("Approved"));
            break;
        case "AddException":
            $config = new Config();
            if ($request->IsPropertySet("ReceiptFrom")) {
                if ($request->GetProperty("ReceiptFrom") == "shop") {
                    if ($request->GetProperty("Vat") == "7") {
                        $code = Config::CODE_VAT_EXCEPTION_7_SHOP;
                    } elseif ($request->GetProperty("Vat") == "19") {
                        $code = Config::CODE_VAT_EXCEPTION_19_SHOP;
                    }
                } elseif ($request->GetProperty("ReceiptFrom") == "restaurant") {
                    $code = Config::CODE_VAT_EXCEPTION_RESTAURANT;
                }

                $data = array(
                    "value" => Config::GetConfigValue($code) . "\n" . $request->GetProperty("Title"),
                    "config_id" => Config::GetIDByCode($code)
                );
                $config->LoadFromArray($data);
                $config->Save();
            }
            break;
        case "GetReceiptCommentListHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_receipt_comment_list.html");

            $receiptCommentList = new ReceiptCommentList($module);
            $receiptCommentList->LoadCommentListForAdmin($request->GetProperty("receipt_id"));
            $content->LoadFromObjectList("ReceiptCommentList", $receiptCommentList);

            $result["HTML"] = $popupPage->Grab($content);
            $result["CommentCount"] = $receiptCommentList->GetCountItems();
            break;
        case "SaveReceiptComment":
            $receiptComment = new ReceiptComment($module);
            $receiptComment->LoadFromObject($request);
            $receiptComment->SetProperty("user_id", $user->GetProperty("user_id"));
            $receiptComment->SetProperty("read_by_admin", "Y");

            if ($receiptComment->Create()) {
                $result["Status"] = "success";
                $link = $moduleURL . "&Section=receipt&receipt_id=" . $request->GetProperty("receipt_id");
                Operation::Save($link, "receipt", "receipt_comment_add", $request->GetProperty("receipt_id"));

                $receipt = new Receipt($module);
                $receipt->LoadByID($request->GetProperty("receipt_id"));
                $receipt->SendReceiptCommentPushNotification();
            } else {
                $result["Status"] = "error";
                $result["Error"] = $receiptComment->GetErrorsAsString();
            }
            break;
        case "GetEmployeeReceiptListHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_employee_receipt_list.html");

            $request->SetProperty("FilterEmployeeID", $request->GetProperty("employee_id"));

            $permission = "receipt";
            if ($user->Validate(array("tax_auditor" => null))) {
                $permission = "tax_auditor";
            }

            $receiptList = new ReceiptList($module);
            $receiptList->SetOrderBy("service_asc_document_date_desc");
            $receiptList->LoadReceiptListForAdmin($request, true, $permission);
            $content->LoadFromObjectList("ReceiptList", $receiptList);

            $filterProductGroup = new ProductGroupList("product");
            $filterProductGroup->LoadProductGroupListForAdmin(true);
            for ($i = 0; $i < $filterProductGroup->GetCountItems(); $i++) {
                if (
                    $filterProductGroup->_items[$i]['group_id'] != $request->GetIntProperty("FilterProductGroup")
                    && $filterProductGroup->GetCountItems() != 1
                ) {
                    continue;
                }

                $filterProductGroup->_items[$i]['Selected'] = true;
            }
            $content->SetLoop("ProductGroupList", $filterProductGroup->GetItems());
            $filterStatusList = array();
            foreach (array("new", "review", "approve_proposed", "approved", "denied", "supervisor") as $status) {
                $filterStatusList[] = array(
                    "title_translation" => GetTranslation("receipt-status-" . $status, $module),
                    "value" => $status,
                    "selected" => strstr($request->GetProperty("FilterStatus1"), $status)
                );
            }
            $content->SetLoop("FilterStatusList", $filterStatusList);
            $content->SetVar('employee_id', $request->GetProperty("employee_id"));
            $content->SetVar('receipt_id', $request->GetProperty("receipt_id"));
            $content->SetVar('FilterCreatedDate', $request->GetProperty("FilterCreatedDate"));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "ReloadTable":
            $filterParams = array(
                "employee_id",
                "FilterStatus1",
                "FilterProductGroup",
                "FilterProductGroup",
                "receipt_id"
            );

            $urlFilter = new URLFilter();
            $urlFilter->LoadFromObject($request, array("Section", "Page"));
            $urlFilter->AppendFromObject($request, $filterParams);

            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_employee_receipt_list_table.html");

            $request->SetProperty("FilterEmployeeID", $request->GetProperty("employee_id"));

            $permission = "receipt";
            if ($user->Validate(array("tax_auditor" => null))) {
                $permission = "tax_auditor";
            }

            $receiptList = new ReceiptList($module);
            $receiptList->SetOrderBy("service_asc_document_date_desc");
            $receiptList->LoadReceiptListForAdmin($request, false, $permission);
            $content->LoadFromObjectList("ReceiptList", $receiptList);

            $content->SetVar(
                "Paging",
                $receiptList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL(array("Page")))
            );
            $content->SetVar("ListInfo", GetTranslation(
                'list-info1',
                array('Page' => $receiptList->GetItemsRange(), 'Total' => $receiptList->GetCountTotalItems())
            ));

            $itemsOnPageList = array();
            foreach (array(10, 20, 50, 100, 0) as $v) {
                $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $receiptList->GetItemsOnPage() ? 1 : 0);
            }
            $content->SetLoop("ItemsOnPageList", $itemsOnPageList);

            $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
            $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
            $content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage", "Page")));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetEmployeeChatListHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_employee_chat_list.html");

            $filterProductGroup = new ProductGroupList("product");
            $filterProductGroup->LoadProductGroupListForAdmin(true);
            for ($i = 0; $i < $filterProductGroup->GetCountItems(); $i++) {
                if (
                    $filterProductGroup->_items[$i]["group_id"] != $request->GetIntProperty("FilterProductGroup")
                    && $filterProductGroup->GetCountItems() != 1
                ) {
                    continue;
                }

                $filterProductGroup->_items[$i]["Selected"] = true;
            }
            if (strlen($request->GetProperty("FilterCreatedRange")) == 0) {
                $content->SetVar("FilterCreatedRange", date("01/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
            }

            $content->SetLoop("ProductGroupList", $filterProductGroup->GetItems());
            $content->SetVar("employee_id", $request->GetProperty("employee_id"));
            $content->SetVar("employee_name", Employee::GetNameByID($request->GetProperty("employee_id")));
            //$content->SetVar("FilterCreatedRangeChat", $request->GetProperty("FilterCreatedRangeChat"));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "ReloadChatTable":
            $filterParams = array(
                "employee_id",
                "FilterCreatedRangeChat",
                "FilterProductGroup"
            );

            $urlFilter = new URLFilter();
            $urlFilter->LoadFromObject($request, array("Section", "Page"));
            $urlFilter->AppendFromObject($request, $filterParams);

            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_employee_chat_list_table.html");

            $permission = "receipt";
            if ($user->Validate(array("tax_auditor" => null))) {
                $permission = "tax_auditor";
            }

            $request->SetProperty("FilterEmployeeID", $request->GetProperty("employee_id"));
            $request->SetProperty("FilterHasChat", "Y");
            $request->SetProperty("AppendChatInfo", "Y");

            $receiptList = new ReceiptList($module);
            $receiptList->SetOrderBy("service_asc_document_date_desc");
            $receiptList->LoadReceiptListForAdmin($request, false, $permission);
            $content->LoadFromObjectList("ReceiptList", $receiptList);

            $content->SetVar(
                "Paging",
                $receiptList->GetPagingAsHTML(
                    $moduleURL . '&' . $urlFilter->GetForURL(array("Page"))
                )
            );
            $content->SetVar(
                "ListInfo",
                GetTranslation(
                    'list-info1',
                    ['Page' => $receiptList->GetItemsRange(), 'Total' => $receiptList->GetCountTotalItems()]
                )
            );

            $itemsOnPageList = array();
            foreach (array(10, 20, 50, 100, 0) as $v) {
                $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $receiptList->GetItemsOnPage() ? 1 : 0);
            }
            $content->SetLoop("ItemsOnPageList", $itemsOnPageList);

            $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
            $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
            $content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage", "Page")));

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "UpdateField":
            Receipt::UpdateField(
                $request->GetProperty("ReceiptID"),
                $request->GetProperty("Field"),
                $request->GetProperty("Value")
            );
            break;
        case "ValidateReceiptUpdateErrors":
            $receipt = new Receipt("receipt");
            $receipt->LoadByID($request->GetIntProperty("receipt_id"));
            $prevStatus = $receipt->GetProperty("status");
            $receipt->AppendFromObject($request);

            $amountApproved = $receipt->GetProperty("amount_approved");
            $amountApproved = preg_match("/[,]/", $amountApproved) ? preg_replace("/[^0-9,]/", "", $amountApproved) : preg_replace("/[^0-9.]/", "", $amountApproved);
            $amountApproved = str_replace(",", ".", $amountApproved);
            $amountApproved = floatval($amountApproved);
            $receipt->SetProperty("amount_approved", $amountApproved);

            $receipt->ValidateUpdate($prevStatus);
            $receipt->SetProperty("Save", 0);
            $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
            if ($specificProductGroup !== null && $receipt->GetProperty("document_date")) {
                $specificProductGroup->ValidateReceiptApprove($receipt);
            }

            if (!in_array($prevStatus, array("review", "new", "supervisor"))) {
                $receipt->RemoveProperty("proposed_denial_reason");
            }

            if ($receipt->HasErrors()) {
                $result = array(
                    "Status" => "error",
                    "ErrorList" => $receipt->GetErrorsAsArray(),
                    "Receipt" => $receipt->GetProperties()
                );
            }
            break;
        case "GetDeniedReceiptListHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_dashboard_receipt_list.html");

            $receiptList = new ReceiptList("receipt");
            $receiptList->SetOrderBy("denial_reason_employee_id_asc");
            $receiptList->LoadReceiptListForDashboard(
                new LocalObject(array(
                "FilterStatus" => array("denied"),
                "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
                "FilterCreatedTo" => $request->GetProperty("DateTo"),
                "FilterDenialReason" => $request->GetProperty("FilterDenialReason"),
                "FilterEmployeeID" => $request->GetProperty("FilterEmployeeID"),
                "GroupBy" => $request->GetProperty("GroupBy")
                )),
                true
            );
            $content->LoadFromObjectList("ReceiptDeniedList", $receiptList);

            $distinctEmployeeMap = array();
            foreach ($receiptList->GetItems() as $item) {
                if (in_array($item["employee_id"], array_column($distinctEmployeeList, "employee_id"))) {
                    continue;
                }

                $distinctEmployeeMap[$item["employee_id"]] = $item["employee_name"];
            }
            $result["DistinctEmployeeMap"] = $distinctEmployeeMap;

            $result["Status"] = "success";
            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "MarkAsReadByAdmin":
            $receiptCommentList = new ReceiptCommentList($module);
            $receiptCommentList->LoadCommentListForAdmin($request->GetProperty("receipt_id"));
            $receiptCommentList->MarkAsReadByAdmin();
            break;
        case "GetTripList":
            $permission = "receipt";
            if ($user->Validate(array("tax_auditor" => null))) {
                $permission = "tax_auditor";
            }

            $receiptList = new ReceiptList("receipt");
            $result = array("TripList" => $receiptList->GetShortReceiptListForAdmin("trip_id", $request, $permission));
            break;
        case "GetOptionsTableHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_receipt_options_table.html");

            $receipt = new Receipt($module);
            $receipt->LoadFromObject($request);

            if (!$receipt->IsPropertySet("save")) {
                $receipt->SetProperty("save", 0);
            }

            $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
            if ($specificProductGroup != null) {
                $code = ProductGroup::GetProductGroupCodeByID($receipt->GetProperty("group_id"));
                $setsOfGoodsServices = [PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__BONUS_VOUCHER];
                if (
                	in_array($code, $setsOfGoodsServices)
                ) {
                    $result = Receipt::GetSetsOfGoodsList($receipt, true);
                    $content->SetLoop(
                        "SetsOfGoodsList",
                        $result["sets_of_goods"]
                    );
                    $content->SetVar("DoNotShowOptionList", 1);
                }

                $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
                if (in_array($receipt->GetProperty("group_id"), array_column($voucherProductGroupList, "group_id"))) {
                    $content->SetVar("ShowVoucherTable", 1);
                }
                $content->SetLoop("ProductGroupOptionList", $specificProductGroup->GetOptions($receipt));
                if (
                    $specificProductGroup instanceof SpecificProductGroupFood
                    && $specificProductGroup->IsValidationFlatTaxRateActive($receipt)
                ) {
                    $content->SetVar(
                        "ValidationFlatTaxRateActive",
                        GetTranslation("service-is-active", "core", [
                            "service" => GetTranslation("product-" . PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION, "product")
                        ])
                    );
                }
            }

            $result["HTML"] = $popupPage->Grab($content);
            break;
        case "GetProcessingDetailsHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_processing_details.html");

            $receipt = new Receipt($module);
            $receipt->LoadByID($request->GetProperty("receipt_id"));

            $request->SetProperty("codes", array("receipt_id", "receipt_id_update"));
            $request->SetProperty("object_id", $receipt->GetProperty("legal_receipt_id"));

            $operationList = new OperationList();
            $operationList->LoadOperationList($request);
            $operationListReverse = array_reverse($operationList->_items);

            $processingList = array();

            $statusValueList = Receipt::GetPropertyValueListReceipt("status", $request->GetProperty("receipt_id"));

            foreach ($operationListReverse as $operation) {
                if ($operation["code"] == "receipt_id") {
                    if (!isset($processingList[$operation["user_id"]]["opening_time"])) {
                        foreach ($statusValueList as $statusValue) {
                            if (strtotime($statusValue["created"]) < strtotime($operation["date"])) {
                                $processingList[$operation["user_id"]]["starting_status"] = GetTranslation(
                                    "receipt-status-" . $statusValue["value"],
                                    $module
                                );
                                break;
                            }
                        }
                        $processingList[$operation["user_id"]]["user_name"] = $operation["user_name"];
                        $processingList[$operation["user_id"]]["opening_time"] = $operation["date"];
                    }
                } else {
                    if (!isset($processingList[$operation["user_id"]]["save_time"])) {
                        foreach ($statusValueList as $statusValue) {
                            if (strtotime($statusValue["created"]) <= strtotime($operation["date"])) {
                                $processingList[$operation["user_id"]]["saved_status"] = GetTranslation(
                                    "receipt-status-" . $statusValue["value"],
                                    $module
                                );
                                break;
                            }
                        }
                        $processingList[$operation["user_id"]]["save_time"] = $operation["date"];
                    }
                }
            }

            foreach ($processingList as $userID => $processing) {
                if (!isset($processing["opening_time"]) || !isset($processing["save_time"])) {
                    continue;
                }

                $openingTime = new DateTime($processing["opening_time"]);
                $saveTime = new DateTime($processing["save_time"]);

                $interval = $saveTime->diff($openingTime);

                $elapsedTime = array();

                if ($interval->format("%a") != 0) {
                    $elapsedTime[] = abs($interval->format("%a")) . "d";
                }

                if ($interval->format("%h") != 0) {
                    $elapsedTime[] = abs($interval->format("%h")) . "h";
                }

                if ($interval->format("%i") != 0) {
                    $elapsedTime[] = abs($interval->format("%i")) . "m";
                }

                if ($interval->format("%s") != 0) {
                    $elapsedTime[] = abs($interval->format("%s")) . "s";
                }

                if ($interval->format("%f") != 0) {
                    $elapsedTime[] = round(abs($interval->format("%f")) / 1000) . "ms";
                }

                $processingList[$userID]["elapsed_time"] = implode(" ", $elapsedTime);
            }

            $content->SetLoop("ProcessingList", array_values($processingList));
            $content->SetVar("LegalReceiptID", $receipt->GetProperty("legal_receipt_id"));

            $result["HTML"] = $popupPage->Grab($content);
            break;
    }
}

echo json_encode($result);
