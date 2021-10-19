<?php

class LocalPage
{
    var $includePaths;
    var $module;
    var $headerTmpl;
    var $footerTmpl;
    var $isAdmin;

    function LocalPage($module)
    {
        $this->includePaths = array();
        $this->module = $module;
    }

    function Load($file, $header = array(), $pageID = null)
    {
        $this->_InitHeader($header, $pageID);
        $this->_InitFooter($header, $pageID);

        return $this->_CreateTemplate($file, $header, $pageID);
    }

    function Output($contentTmpl)
    {
        $this->headerTmpl->pparse();
        $contentTmpl->pparse();
        $this->footerTmpl->pparse();
    }

    function Grab($contentTmpl)
    {
        $header = $this->headerTmpl->Grab();
        $content = $contentTmpl->Grab();
        $footer = $this->footerTmpl->Grab();

        return $header . $content . $footer;
    }

    function _InitHeader($header, $pageID)
    {
        $this->headerTmpl = isset($header['HeaderTemplate']) ? $this->_CreateTemplate($header['HeaderTemplate'], $header, $pageID) : $this->_CreateTemplate("_header.html", $header, $pageID);
        $this->headerTmpl->LoadFromArray($header);
        $this->headerTmpl->SetVar("PageID", $pageID);

        $session =& GetSession();
        $request = new LocalObject(array_merge($_GET, $_POST));
        $this->headerTmpl->SetVar("EditVariables", $session->GetProperty("EditVariables"));
        $this->headerTmpl->SetVar("ActiveTab", $request->GetProperty("ActiveTab"));
        $this->headerTmpl->SetVar("ScrollTop", $request->GetProperty("ScrollTop"));

        $user = new User();
        $user->LoadBySession();

        if ($user->Validate(array("root"))) {
            $this->headerTmpl->SetVar("Admin", "Y");
        }

        $this->headerTmpl->SetVar(
            "LNG_BenefitPortalForTheCompany",
            GetTranslation("benefit-portal-for-the-company", "core")
        );
        $this->headerTmpl->SetVar("CompanyTitle", $user->GetProperty("belongs_to_company"));
        $this->headerTmpl->SetVar("CompanyLogo", $user->GetCompanyUnitLogo());

        if (!IsTestEnvironment() && !IsLocalEnvironment()) {
            return;
        }

        $this->headerTmpl->SetVar("AllowEditVariables", 1);
    }

    function _InitFooter($header, $pageID)
    {
        $this->footerTmpl = isset($header['FooterTemplate']) ? $this->_CreateTemplate($header['FooterTemplate'], $header, $pageID) : $this->_CreateTemplate("_footer.html", $header, $pageID);
        $this->footerTmpl->LoadFromArray($header);
        $this->footerTmpl->SetVar("PageID", $pageID);
    }

    function _CreateTemplate($file, $header, $pageID)
    {
        $tmpl = new Template($file, array("INCLUDE_PATHS" => $this->includePaths));

        if ($this->isAdmin) {
            $tmpl->SetVar("PATH2MAIN", ADMIN_PATH . "template/");
            if (!is_null($this->module)) {
                $tmpl->SetVar("MODULE_NAME", $this->module);
                $tmpl->SetVar(
                    "MODULE_URL",
                    GetFromConfig("ProtocolType") . $_SERVER["HTTP_HOST"] . ADMIN_PATH . "module.php?load=" . $this->module
                );
                $tmpl->SetVar("MODULE_PATH", PROJECT_PATH . 'module/' . $this->module . '/');
                $tmpl->SetVar("PATH2MOD", PROJECT_PATH . "module/" . $this->module . "/template/");
            }
            $tmpl->SetVar("CMS_BRAND", GetFromConfig("Brand"));
        } else {
            $tmpl->SetVar("PATH2MAIN", PROJECT_PATH . "website/" . WEBSITE_FOLDER . "/template/");
        }

        if (strlen($GLOBALS["WebsiteLogo"]) > 0 && is_file(PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/" . $GLOBALS["WebsiteLogo"])) {
            $tmpl->SetVar(
                "WEBSITE_LOGO",
                PROJECT_PATH . "website/" . WEBSITE_FOLDER . "/template/" . $GLOBALS["WebsiteLogo"]
            );
        }

        /**@var language Language */
        $language =& GetLanguage();
        $translation = $language->LoadForTempate($file, $this->module, $this->isAdmin);

        $session =& GetSession();

        foreach ($translation as $key => $value) {
            if (
                $session->GetProperty("EditVariables") && isset($value["Value"]) && in_array(
                    "editable",
                    $value["AttributeList"]
                )
            ) {
                $tmpl->SetVar(
                    "LNG_" . $key,
                    $value["Value"] . "#variable_" . $value["AttributeList"]["variable_id"] . "#"
                );
            } elseif (isset($value["Value"])) {
                $tmpl->SetVar("LNG_" . $key, $value["Value"]);
            }
        }

        return $tmpl;
    }
}

class AdminPage extends LocalPage
{
    function AdminPage($module = null)
    {
        parent::LocalPage($module);

        $this->isAdmin = true;

        if (!is_null($this->module)) {
            array_push($this->includePaths, PROJECT_DIR . "module/" . $this->module . "/template/");
        }
        array_push($this->includePaths, PROJECT_DIR . ADMIN_FOLDER . "/template/");
    }

    function _InitHeader($header, $pageID)
    {
        parent::_InitHeader($header, $pageID);

        $adminMenu = self::GetAdminMenu($header);

        for ($i = 0; $i < count($adminMenu); $i++) {
            if ($header['Navigation'][0]['Link'] != $adminMenu[$i]["Link"]) {
                continue;
            }

            $adminMenu[$i]["Selected"] = true;
        }
        $this->headerTmpl->SetLoop("AdminMenu", $adminMenu);
    }

    static function GetAdminMenu($header)
    {
        $user = new User();
        $user->LoadBySession();

        $adminMenu = array();

        if ($user->Validate(array("receipt" => null)) || $user->Validate(array("tax_auditor" => null))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-receipt"),
                "Link" => "module.php?load=receipt&Section=receipt",
                "AdminMenuIcon" => "fa fa-tachometer-alt"
            );
        }
        if ($user->Validate(array("support"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-support"),
                "Link" => "underconstruction.php?p=2",
                "AdminMenuIcon" => "fa fa-envelope"
            );
        }
        if ($user->Validate(array("company_unit" => null, "contract" => null), "or")) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-orgunit"),
                "Link" => "module.php?load=company&Section=company_unit",
                "AdminMenuIcon" => "fa fa-puzzle-piece"
            );
        }
        if ($user->Validate(array("employee" => null, "employee_view" => null), "or")) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-employees"),
                "Link" => "module.php?load=company&Section=employee",
                "AdminMenuIcon" => "fa fa-child"
            );
        }
        if ($user->Validate(array("invoice" => null)) && !$user->Validate(array("root"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-reports"),
                "Link" => "module.php?load=billing&Section=invoice",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
        }
        if ($user->Validate(array("payroll" => null, "tax_auditor" => null), "or") && !$user->Validate(array("root"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-payroll"),
                "Link" => "module.php?load=billing&Section=payroll",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
        }
        if (
            $user->Validate(
                array("bookkeeping_export" => null, "tax_auditor" => null),
                "or"
            ) && !$user->Validate(array("root"))
        ) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-bookkeeping-export"),
                "Link" => "module.php?load=billing&Section=bookkeeping_export",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
        }
        if ($user->Validate(array("partner" => null)) && !$user->Validate(array("root"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-partner"),
                "Link" => "module.php?load=partner",
                "AdminMenuIcon" => "fa fa-handshake"
            );
        }
        if ($user->Validate(array("stored_data" => null)) && !$user->Validate(array("root"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-stored-data"),
                "Link" => "module.php?load=billing&Section=stored_data",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
        }
        if ($user->Validate(array("root"))) {
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-partner"),
                "Link" => "module.php?load=partner",
                "AdminMenuIcon" => "fa fa-handshake"
            );
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-reports"),
                "Link" => "module.php?load=billing&Section=invoice",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-payroll"),
                "Link" => "module.php?load=billing&Section=payroll",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-stored-data"),
                "Link" => "module.php?load=billing&Section=stored_data",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-bookkeeping-export"),
                "Link" => "module.php?load=billing&Section=bookkeeping_export",
                "AdminMenuIcon" => "fa fa-file-alt"
            );
            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-master-data-export"),
                "Link" => "module.php?load=billing&Section=master_data",
                "AdminMenuIcon" => "fa fa-file-alt"
            );

            $userLink = "user.php";
            $configLink = "config.php";
            $productSettingsLink = "module.php?load=product";
            $logLink = "logging.php";
            $cronLogLink = "logging_cron.php";
            $pushHistoryLink = "push_history.php";
            $dashboardLink = "dashboard.php";
            $techDashboardLink = "technical_dashboard.php";
            $voucherDashboardLink = "voucher_dashboard.php";
            $processingDashboardLink = "processing_dashboard.php";

            if (isset($header["Navigation"]) && isset($header["Navigation"][0]["Link"])) {
                $navigation = $header["Navigation"][0]["Link"];
                $userStr = substr($navigation, 0, strlen($userLink) + 1);
                $configStr = substr($navigation, 0, strlen($configLink) + 1);
                $productSettingsStr = substr($navigation, 0, strlen($productSettingsLink) + 1);
                $logStr = substr($navigation, 0, strlen($logLink) + 1);
                $cronLogStr = substr($navigation, 0, strlen($cronLogLink) + 1);
                $pushHistoryStr = substr($navigation, 0, strlen($pushHistoryLink) + 1);

                $userSelected = $navigation == $userLink || substr($userStr, -1) == '&';

                $configSelected = $navigation == $configLink || substr($configStr, -1) == '&';

                $productSettingsSelected = $navigation == $productSettingsLink || substr($productSettingsStr, -8) == 'product&';

                $logSelected = $navigation == $logLink || substr($logStr, -1) == '&';

                $cronLogSelected = $navigation == $cronLogLink || substr($cronLogStr, -1) == '&';

                $pushHistorySelected = $navigation == $pushHistoryLink || substr($pushHistoryStr, -1) == '&';

                $dashboardSelected = $navigation == $dashboardLink;

                $techDashboardSelected = $navigation == $techDashboardLink;

                $voucherDashboardSelected = $navigation == $voucherDashboardLink;

                $processingDashboardSelected = $navigation == $processingDashboardLink;
            } else {
                $userSelected = false;
                $configSelected = false;
                $productSettingsSelected = false;
                $logSelected = false;
                $cronLogSelected = false;
                $pushHistorySelected = false;
                $dashboardSelected = false;
                $techDashboardSelected = false;
                $voucherDashboardSelected = false;
                $processingDashboardSelected = false;
            }

            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-main-settings"),
                "Link" => "",
                "AdminMenuIcon" => "fa fa-cogs",
                "Selected" => $userSelected || $configSelected || $productSettingsSelected,
                "Submenu" => array(
                    array(
                        "Title" => GetTranslation("admin-menu-user-list"),
                        "Link" => "user.php",
                        "AdminMenuIcon" => "fa fa-child",
                        "Selected" => $userSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-config"),
                        "Link" => "config.php",
                        "AdminMenuIcon" => "fa fa-wrench",
                        "Selected" => $configSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-settings"),
                        "Link" => "module.php?load=product",
                        "AdminMenuIcon" => "fa fa-cogs",
                        "Selected" => $productSettingsSelected
                    )
                )
            );

            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-main-logging"),
                "Link" => "",
                "AdminMenuIcon" => "fa fa-tasks",
                "Selected" => $logSelected || $cronLogSelected || $pushHistorySelected,
                "Submenu" => array(
                    array(
                        "Title" => GetTranslation("admin-menu-logging"),
                        "Link" => $logLink,
                        "AdminMenuIcon" => "fa fa-tasks",
                        "Selected" => $logSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-push-log"),
                        "Link" => $pushHistoryLink,
                        "AdminMenuIcon" => "fa fa-bell",
                        "Selected" => $pushHistorySelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-cron-logging"),
                        "Link" => $cronLogLink,
                        "AdminMenuIcon" => "fa fa-tasks",
                        "Selected" => $cronLogSelected
                    ),
                )
            );

            $adminMenu[] = array(
                "Title" => GetTranslation("admin-menu-main-dashboard"),
                "Link" => "dashboard.php",
                "AdminMenuIcon" => "fa fa-pie-chart",
                "Selected" => $dashboardSelected || $techDashboardSelected || $voucherDashboardSelected || $processingDashboardSelected,
                "Submenu" => array(
                    array(
                        "Title" => GetTranslation("admin-menu-dashboard"),
                        "Link" => $dashboardLink,
                        "AdminMenuIcon" => "fa fa-pie-chart",
                        "Selected" => $dashboardSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-technical_dashboard"),
                        "Link" => $techDashboardLink,
                        "AdminMenuIcon" => "fa fa-pie-chart",
                        "Selected" => $techDashboardSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-voucher_dashboard"),
                        "Link" => $voucherDashboardLink,
                        "AdminMenuIcon" => "fa fa-pie-chart",
                        "Selected" => $voucherDashboardSelected
                    ),
                    array(
                        "Title" => GetTranslation("admin-menu-processing_dashboard"),
                        "Link" => $processingDashboardLink,
                        "AdminMenuIcon" => "fa fa-pie-chart",
                        "Selected" => $processingDashboardSelected
                    )
                )
            );
        }

        return $adminMenu;
    }
}

class PublicPage extends LocalPage
{
    function PublicPage($module = null)
    {
        parent::LocalPage($module);

        $this->isAdmin = false;

        array_push(
            $this->includePaths,
            PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/" . INTERFACE_LANGCODE . "/"
        );
        array_push($this->includePaths, PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/");
    }
}

class PopupPage extends LocalPage
{
    function PopupPage($module = null, $isAdmin = true)
    {
        parent::LocalPage($module);

        $this->isAdmin = $isAdmin;

        if ($isAdmin) {
            if (!is_null($this->module)) {
                array_push($this->includePaths, PROJECT_DIR . "module/" . $this->module . "/template/");
            }
            array_push($this->includePaths, PROJECT_DIR . ADMIN_FOLDER . "/template/");
        } else {
            array_push(
                $this->includePaths,
                PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/" . INTERFACE_LANGCODE . "/"
            );
            array_push($this->includePaths, PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/");
        }
    }

    function Load($file, $header = array(), $pageID = null)
    {
        $tmpl = $this->_CreateTemplate($file, $header, $pageID);
        $tmpl->LoadFromArray($header);
        $tmpl->SetVar("PageID", $pageID);

        return $tmpl;
    }

    function Output($contentTmpl)
    {
        $contentTmpl->pparse();
    }

    function Grab($contentTmpl)
    {
        return $contentTmpl->Grab();
    }
}
