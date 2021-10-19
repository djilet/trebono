<?php

if (!defined('IS_ADMIN')) {
    echo "Incorrect call to admin interface";
    exit();
}

require_once(dirname(__FILE__) . "/init.php");

$user = new User();

$module = $request->GetProperty("load");
$adminPage = new AdminPage($module);
$navigation = array(array("Title" => GetTranslation("module-title", $module), "Link" => $moduleURL));

if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", "option");
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, array("Section"));

$sectionList = array("option", "product_group");

$templateSectionList = array();
foreach ($sectionList as $section) {
    $sectionTitle = GetTranslation("section-" . $section, $module);
    $templateSectionList[] = array(
        "Section" => $section,
        "Title" => $sectionTitle,
        "Selected" => ($request->GetProperty("Section") == $section ? 1 : 0)
    );
}

require_once(dirname(__FILE__) . "/controller/" . $request->GetProperty("Section") . ".php");

if (isset($content)) {
    $content->SetLoop("Navigation", $navigation);
    $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
    $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
    $content->SetLoop("SectionList", $templateSectionList);
    $content->SetVar("Section", $request->GetProperty("Section"));
    $content->SetVar(
        "ReturnPath",
        urlencode(ADMIN_PATH . "module.php?load=" . $module . "&Section=" . $request->GetProperty("Section"))
    );
    $adminPage->Output($content);
}
