<?php

if (!defined("IS_ADMIN")) {
    echo "Incorrect call to admin interface";
    exit();
}
require_once(dirname(__FILE__) . "/init.php");

$module = $request->GetProperty("load");
$adminPage = new AdminPage($module);
$navigation = [[
    "Title" => GetTranslation("module-title", $module),
    "Link" => "module.php?load=company&Section=company_unit",
]];

if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", "Contracts");
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, ["Section", "OrganizationID", "CompanyUnitID"]);


require_once(dirname(__FILE__) . "/controller/" . strtolower($request->GetProperty("Section")) . ".php");

if (isset($content)) {
    $user = new User();
    $user->LoadBySession();
    if ($user->Validate(["root"])) {
        $content->SetVar("Admin", "Y");
    }
    if ($user->Validate(["root", "company_unit" => null, "employee" => null], "or")) {
        $content->SetVar("HistoryAdmin", "Y");
    }

    $content->SetLoop("Navigation", $navigation);
    $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
    $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
    $content->SetVar("Section", $request->GetProperty("Section"));
    $content->SetVar("OrganizationID", $request->GetProperty("OrganizationID"));
    $content->SetVar("CompanyUnitID", $request->GetProperty("CompanyUnitID"));
    $adminPage->Output($content);
}
