<?php

define("COMPANY_IMAGE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/");
define("COMPANY_COMPANY_IMAGE", "100x100|8|Admin,100x100|0|Full,300x300|8|Api");

define("COMPANY_APP_LOGO_IMAGE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/apps/");
define("COMPANY_APP_LOGO_IMAGE", "200x100|1|admin,300x150|1|api,300x150|100|pdf");
define("COMPANY_APP_LOGO_MINI_IMAGE", "200x100|1|admin,300x150|1|api");

define("COMPANY_VOUCHER_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/voucher/");
define("COMPANY_VOUCHER_LOGO_IMAGE", "140x70|1|admin,300x150|1|api");

define("CONTAINER__COMPANY", "");
define("COMPANY_UNIT_DOCUMENT_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/contract/");
define("COMPANY_UNIT_YEARLY_REPORT_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/report/");

$GLOBALS['moduleConfig']['company'] = array(
    'ColorA' => '#000',
    'ColorI' => '#000',
    'Config' => array()
);
