<?php

define("SCRIPT_START_MICROTIME", microtime(true));

// PROJECT_DIR is used in es_include() function, so it must be defined before function
define("PROJECT_DIR", realpath(dirname(__FILE__) . "/../") . "/");
define("VLIB_CACHE_DIR", PROJECT_DIR . "var/cache/");
define("XML_CACHE_DIR", PROJECT_DIR . "var/xml/");
define("VAR_DIR", PROJECT_DIR . "var/");
define("CONTAINER__CORE", "");
define("CONTAINER__TEST", "_test");

// genlib.php must be included here to define GetFromConfig() function
function es_include($fileName)
{
    require_once(PROJECT_DIR . "include/" . $fileName);
}

es_include("genlib.php");

// Add fokcms autoloader
es_include("autoloader.php");
spl_autoload_register("Autoloader::Load");

// Add composer autoloader
require_once PROJECT_DIR . "vendor/autoload.php";

// Define timezone (before ErrorHandler because ErrorHandler is using date functions)
$timeZone = 'GMT';
if (is_file(dirname(__FILE__) . '/../timezone.txt')) {
    $lines = file(dirname(__FILE__) . '/../timezone.txt');
    if (is_array($lines) && count($lines) > 0 && strlen(trim($lines[0])) > 0) {
        $timeZone = $lines[0];
    }
}
date_default_timezone_set($timeZone);

// Set error handler
require_once(dirname(__FILE__) . "/error_handler/error_handler.php");
ErrorHandler::SetErrorHandler();

function RemoveQuotes($variable)
{
    if (is_array($variable)) {
        foreach ($variable as $key => $value) {
            $variable[$key] = RemoveQuotes($value);
        }
    } else {
        $variable = stripslashes($variable);
    }

    return $variable;
}

if (get_magic_quotes_gpc()) {
    $_POST = RemoveQuotes($_POST);
    $_GET = RemoveQuotes($_GET);
    $_COOKIE = RemoveQuotes($_COOKIE);
    $_REQUEST = RemoveQuotes($_REQUEST);
}

// Cookie expire (before new Website() because constructor of Website is using this parameter)
define("COOKIE_EXPIRE", 3);

define("ADMIN_FOLDER", "admin");

// Identify website
$website = new Website();

define("ADMIN_PATH", PROJECT_PATH . ADMIN_FOLDER . "/");
define("ADMIN_PATH_ABSOLUTE", dirname(__DIR__) . '/' . ADMIN_FOLDER . '/');

// Index page (after new Website() because WEBSITE_ID is defined constructor of class Website and it is needed to load website configuration)
$indexPage = GetFromConfig("IndexPage");
if (is_null($indexPage) || strlen($indexPage) == 0) {
    define("INDEX_PAGE", "index");
} else {
    define("INDEX_PAGE", $indexPage);
}

// HTML Extension (after new Website() because WEBSITE_ID is defined constructor of class Website and it is needed to load website configuration)
$htmlExtension = GetFromConfig("HTMLExtension");
if (is_null($htmlExtension) || strlen($htmlExtension) == 0) {
    define("HTML_EXTENSION", ".html");
} else {
    define("HTML_EXTENSION", $htmlExtension);
}

// Other paths (after new Website() because PROJECT_PATH is defined constructor of class Website)
define("CKEDITOR_PATH", ADMIN_PATH . "template/plugins/ckeditor/");

define("MENU_IMAGE_PATH", PROJECT_PATH . "website/" . WEBSITE_FOLDER . "/var/page/");
define("MENU_IMAGE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/page/");
define("USER_IMAGE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/user/");
define("INVOICE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/billing/invoice/");
define("INVOICE_ARCHIVE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/billing/invoice/archive/");
define("INVOICE_TMP_DIR", PROJECT_DIR . "var/log/invoice/");
define("REPORT_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/partner/report/");
define("API_LOG_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/receipt/log/");
define("CONFIG_FILE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/config/");
define("PAYROLL_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/payroll/");
define("PAYROLL_TMP_DIR", PROJECT_DIR . "var/log/payroll/");
define("MASTER_DATA_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/billing/master_data/");
define("VOUCHER_EXPORT_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/billing/voucher_export/");
define("EXPORT_INVOICE_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/billing/export_invoice/");
define("STORED_DATA_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/stored_data/");
define("BOOKKEEPING_EXPORT_DIR", PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/company/bookkeeping_export/");
define("BOOKKEEPING_EXPORT_TMP_DIR", PROJECT_DIR . "var/log/bookkeeping_export/");

$GLOBALS['moduleConfig'] = array();

define("DB_MAIN", "main");
define("DB_PERSONAL", "personal");
define("DB_CONTROL", "control");

// Initial database connections
GetStatement(DB_MAIN);
GetStatement(DB_PERSONAL);
GetStatement(DB_CONTROL);

// Include all module init scripts
$module = new Module();
foreach ($module->GetModuleList() as $m) {
    require_once(PROJECT_DIR . "module/" . $m["Folder"] . "/init.php");
}

define("BILLING_USER_ID", -1); //Fixed user_id for Billing System user
define("SERVICE_USER_ID", -2); //Fixed user_id for Fineasy Service user
define("AZ_IMPORT", -3); //Fixed user_id for AZ import user
define("SERVICE_RG", -4); //Fixed user_id for Invoice create
define("GUTSCHEIN_RG", -5); //Fixed user_id for Voucher invoice create
define("DATENSICHERUNG", -6); //Fixed user_id for Stored data create
define("SB_GUTSCHEINE", -7); //Fixed user_id for Create BVS vouchers
define("ESSEN_GUTSCHEINE", -8); //Fixed user_id for Create FVS vouchers
define("DAUERGUTSCHEINE", -9); //Fixed user_id for Voucher generation
define("OCR", -10); //Fixed user_id for OCR check
define("Lohn", -11); //Fixed user_id for Payroll create
define("AUTO_ADOPTION", -12); //Fixed user_id for Auto adoption
define("EMPLOYEE_FILTER_COUNT_FOOD", 50); //Restriction for employee filter which requires getting statistics (food product)
define("EMPLOYEE_FILTER_COUNT", 500); //Restriction for employee filter which requires getting statistics
