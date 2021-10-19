<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess([]);

$request = new LocalObject(array_merge($_GET, $_POST));

$adminPage = new AdminPage();

$userList = new UserList();
$user = new User();

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject(
    $request,
    [$userList->GetPageParam(), $userList->GetOrderByParam(), "SearchString"]
);
$urlString = $urlFilter->GetForURL();

$filterParams = ["filter_user",
    "FilterArchive",
    "filter_company_unit",
    "filter_from_company_unit"];

if ($request->IsPropertySet("user_id")) {
    $title = $user->LoadByID($request->GetProperty("user_id"))
        ? GetTranslation("title-user-edit")
        : GetTranslation("title-user-add");

    if ($request->GetProperty("user_id") == $auth->GetProperty("user_id")) {
        $navigation = [
            ["Title" => $title, "Link" => "user.php?user_id=" . $request->GetProperty("user_id")],
        ];
    } else {
        $navigation = [
            ["Title" => GetTranslation("title-user-list"), "Link" => "user.php"],
            ["Title" => $title, "Link" => "user.php?UserID=" . $request->GetProperty("UserID")],
        ];
    }
    $header = [
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => [
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/typeahead/css/typeahead.css"],
        ],
        "JavaScripts" => [
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/inputmask/jquery.inputmask.bundle.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-validation/js/jquery.validate.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/handlebars.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/typeahead.bundle.js"],
        ],
    ];
    $content = $adminPage->Load("user_edit.html", $header);

    $cloudAdmin = false;
    $permissionList = $auth->GetProperty("PermissionList");
    foreach ($permissionList as $permission) {
        if ($permission["name"] == "root") {
            $cloudAdmin = true;
            $content->SetVar("CloudAdmin", true);
            $content->SetVar("Admin", "Y");
            $content->SetVar("HistoryAdmin", "Y");
            break;
        }
    }

    if ($request->GetProperty("user_id")) {
        $link = "user.php?user_id=" . $request->GetProperty("user_id");
        Operation::Save($link, "user", "user_id", $request->GetProperty("user_id"));
    }

    $content->SetLoop("Navigation", $navigation);

    if ($request->GetProperty("Do") == "Save") {
        // Append instead of Load to avoid lost of Created, LastLogin & LastIP fields data
        $user->AppendFromObject($request);

        if ($user->Save($auth->GetProperty("user_id"), $cloudAdmin)) {
            if ($request->GetProperty("user_id") != $auth->GetProperty("user_id")) {
                //header("Location: ".ADMIN_PATH."user.php".($urlString ? "?".$urlString : ""));
                $link = "user.php?user_id=" . $user->GetProperty("user_id");
                Operation::Save($link, "user", "user_id_save", $user->GetProperty("user_id"));
                //exit;
            }
        }
    }

    if ($auth->GetProperty("user_id") == $user->GetProperty("user_id")) {
        $content->SetVar("MyProfile", true);
    }

    $content->LoadErrorsFromObject($user);
    $content->LoadMessagesFromObject($user);
    $content->LoadFromObject($user);
    $content->SetLoop("UserImageParamList", $user->GetImageParams("user"));

    $availablePermissionList = $user->GetGroupedAvailablePermissions($cloudAdmin);
    $content->SetLoop("AvailablePermissionList", $availablePermissionList);

    $companyUnitList = new CompanyUnitList("company");
    $companyUnitList->LoadCompanyUnitListForTree(null, "company_unit", "N", false, true);
    $content->LoadFromObjectList("CompanyUnitList", $companyUnitList);

    $productGroupList = new ProductGroupList("product");
    $productGroupList->LoadProductGroupListForAdmin();
    $content->LoadFromObjectList("ProductGroupList", $productGroupList);

    $partnerModule = "partner";
    $partnerList = new PartnerList($partnerModule);
    $partnerList->LoadPartnerList($request, false, true);

    for ($j = 0; $j < $partnerList->GetCountItems(); $j++) {
        $contactList = new PartnerContactList($partnerModule);
        $contactList->LoadContactList($partnerList->_items[$j]["partner_id"]);
        $partnerList->_items[$j]["ContactList"] = $contactList->GetItems();
    }

    $content->LoadFromObjectList("PartnerList", $partnerList);

    $archiveInfo = $user->LoadArchiveInfo();
    if ($archiveInfo) {
        $messageObject = new CommonObject();
        foreach ($archiveInfo as $line) {
            $message = $line["value"] == "Y" ? "user-archive-y-message" : "user-archive-n-message";

            $messageObject->AddMessage(
                $message,
                null,
                [
                    "entity" => GetTranslation("entity-user"),
                    "username" => $line["username"],
                    "datetime" => (new DateTime($line["created"]))->format("H:i:s d M Y"),
                ]
            );
        }
        $content->SetLoop("ArchiveMessage", $messageObject->GetMessagesAsArray());
    }
} else {
    $urlFilter->AppendFromObject($request, $filterParams);

    $title = GetTranslation("title-user-list");

    $navigation = [
        ["Title" => $title, "Link" => "user.php"],
    ];
    $header = [
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => [
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/typeahead/css/typeahead.css"],
        ],
        "JavaScripts" => [
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/handlebars.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/typeahead.bundle.js"],
        ],
    ];
    $content = $adminPage->Load("user_list.html", $header);

    $link = "user.php";
    Operation::Save($link, "user", "user_list");

    $content->SetLoop("Navigation", $navigation);

    $request->SetProperty("CurrentUserID", $auth->GetProperty("user_id"));

    if ($request->GetProperty("Do") == "Remove") {
        $userList->Remove($request);
        $content->LoadMessagesFromObject($userList);
        Operation::Save($link, "user", "user_inactivate");
    }
    if ($request->GetProperty("Do") == "Activate") {
        $userList->Activate($request);
        $content->LoadMessagesFromObject($userList);
        Operation::Save($link, "user", "user_activate");
    }

    $session = GetSession();
    foreach ($filterParams as $key) {
        if ($session->IsPropertySet($key) && !$request->IsPropertySet($key)) {
            $request->SetProperty($key, $session->GetProperty($key));
        } else {
            $session->SetProperty($key, $request->GetProperty($key));
        }
    }
    $session->SaveToDB();

    // TODO: OrderBy
    $userList->LoadUserList($request);
    $content->LoadFromObjectList("UserList", $userList);

    $companyUnitList = new CompanyUnitList("company");
    $companyUnitList->LoadCompanyUnitLinearList($request, true);

    $content->LoadFromObjectList("CompanyUnitList", $companyUnitList);

    $pagingULRString = $urlFilter->GetForURL([$userList->GetPageParam()]);
    $url = "user.php" . ($pagingULRString ? "?" . $pagingULRString : "");
    $content->SetVar("Paging", $userList->GetPagingAsHTML($url));
    if ($request->GetProperty('SearchString')) {
        $content->SetVar(
            "ListInfo",
            GetTranslation(
                'list-info2',
                ['Request' => $request->GetProperty('SearchString'), 'Total' => $userList->GetCountTotalItems()]
            )
        );
    } else {
        $content->SetVar(
            "ListInfo",
            GetTranslation(
                'list-info1',
                ['Page' => $userList->GetItemsRange(), 'Total' => $userList->GetCountTotalItems()]
            )
        );
    }
    $content->LoadFromObject($request, $filterParams);
    if (!$request->IsPropertySet("FilterArchive")) {
        $content->SetVar("FilterArchive", "N");
    }
}

if ($urlString) {
    $content->SetVar("ParamsForURL1", "?" . $urlString);
    $content->SetVar("ParamsForURL2", "&" . $urlString);
}
$content->SetVar("ParamsForForm", $urlFilter->GetForForm());

$content->LoadFromObject($urlFilter);

$adminPage->Output($content);
