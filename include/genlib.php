<?php

es_include("postgresql/connection.php");

function GetDatabaseCredentials($dbType)
{
    $credentials = array(
        "Host" => "",
        "User" => "",
        "Password" => "",
        "Database" => ""
    );

    if ($services = getenv("VCAP_SERVICES")) {
        $services = json_decode($services, true);
        if (isset($services["osb-postgresql"])) {
            foreach ($services["osb-postgresql"] as $pg) {
                if ($pg["name"] != "lst_" . $dbType) {
                    continue;
                }

                $params = parse_url($pg["credentials"]["uri"]);
                $credentials["Host"] = $params["host"];
                $credentials["User"] = $pg["credentials"]["user"];
                $credentials["Password"] = $pg["credentials"]["password"];
                $credentials["Database"] = $pg["credentials"]["database"];
            }
        }
    } else {
        $credentials["Host"] = GetFromConfig("Host", "sql_" . $dbType);
        $credentials["User"] = GetFromConfig("User", "sql_" . $dbType);
        $credentials["Password"] = GetFromConfig("Password", "sql_" . $dbType);
        $credentials["Database"] = GetFromConfig("Database", "sql_" . $dbType);
    }

    return $credentials;
}

function &GetConnection($dbType): Connection
{
    static $instance;

    if (isset($instance[$dbType])) {
        $instance[$dbType]->ReconnectIfNeeded();
    } else {
        $language =& GetLanguage();
        $className = $dbType == DB_MAIN ? "Connection" : "Connection" . ucfirst($dbType);

        $credentials = GetDatabaseCredentials($dbType);
        $instance[$dbType] = new $className(
            $credentials["Host"],
            $credentials["Database"],
            $credentials["User"],
            $credentials["Password"],
            $language->GetMySQLEncoding()
        );
    }

    return $instance[$dbType];
}

function GetStatement($dbType = DB_MAIN)
{
    $instance = GetConnection($dbType);

    return $instance->CreateStatement(PGSQL_ASSOC, E_USER_WARNING);
}

function GetFileStorage($container): FileStorageInterface
{
    if (getenv("VCAP_SERVICES") || GetFromConfig("Type", "file_storage") == "swift") {
        $fileStorage = GetFromConfig("Swift", "file_storage") == "s3" ? new FileStorageS3() : new FileStorageSwift();
        $fileStorage->SetContainer($container);

        return $fileStorage;
    }

    return new FileSys();
}

function &GetLanguage()
{
    static $language;
    if (is_null($language)) {
        $language = new Language();
    }

    return $language;
}

function &GetURLParser()
{
    static $parser;
    if (is_null($parser)) {
        $parser = new URLParser();
    }

    return $parser;
}

function GetTranslation($key, $module = null, $replacements = [], $forceLanguage = null)
{
    $language =& GetLanguage();

    if (is_array($module)) {
        $replacements = $module;
        $module = null;
    }

    return $language->GetTranslation($key, $module, $replacements, $forceLanguage);
}

function &GetSession()
{
    static $session;
    if (is_null($session)) {
        $session = new Session("sm");
    }

    return $session;
}

function GetFromConfig($param, $section = "common")
{
    static $websiteConfig;

    if (is_null($websiteConfig) && defined("WEBSITE_FOLDER")) {
        $configFile = dirname(__FILE__) . "/../website/" . WEBSITE_FOLDER . "/configure.ini";
        if (is_file($configFile)) {
            $websiteConfig = parse_ini_file($configFile, true);
        }
    }

    return $websiteConfig[$section][$param] ?? null;
}

function LocalDate($format, $timeStamp = null)
{
    $text = array('F', 'M', 'l', 'D');
    $found = array();

    // Find text representations of week & month in date format
    for ($i = 0; $i < count($text); $i++) {
        $pos = strpos($format, $text[$i]);
        if ($pos === false || substr($format, $pos - 1, 1) == "\\") {
            continue;
        }

        $format = str_replace($text[$i], "__\\" . $text[$i] . "__", $format);
        $found[] = $text[$i];
    }

    $result = is_null($timeStamp) ? date($format) : date($format, $timeStamp);

    // For found text representations replace it by correct language
    for ($i = 0; $i < count($found); $i++) {
        $textInLang = is_null($timeStamp) ? GetTranslation("date-" . date($found[$i])) : GetTranslation("date-" . date($found[$i], $timeStamp));
        $result = str_replace("__" . $found[$i] . "__", $textInLang, $result);
    }

    return $result;
}

function GetGermanMonthName($month)
{
    $monthMap = array(
        1 => "Januar",
        2 => "Februar",
        3 => "März",
        4 => "April",
        5 => "Mai",
        6 => "Juni",
        7 => "Juli",
        8 => "August",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Dezember"
    );

    $month = intval($month);

    return $monthMap[$month] ?? null;
}

function GetAgoString($dateTime)
{
    $time = strtotime($dateTime);
    $currentTime = strtotime(GetCurrentDateTime());
    $diff = $currentTime - $time;

    $intervalList = array(
        array("Seconds" => 60 * 60 * 24 * 365, "Singular" => "year", "Plural" => "years"),
        array("Seconds" => 60 * 60 * 24 * 30, "Singular" => "month", "Plural" => "months"),
        array("Seconds" => 60 * 60 * 24, "Singular" => "day", "Plural" => "days"),
        array("Seconds" => 60 * 60, "Singular" => "hour", "Plural" => "hours"),
        array("Seconds" => 60, "Singular" => "minute", "Plural" => "minutes"),
        array("Seconds" => 1, "Singular" => "second", "Plural" => "seconds")
    );

    foreach ($intervalList as $interval) {
        if (intval($diff / $interval["Seconds"]) > 1) {
            return intval($diff / $interval["Seconds"]) . " " . GetTranslation("ago-" . $interval["Plural"]);
        }

        if (intval($diff / $interval["Seconds"]) == 1) {
            return intval($diff / $interval["Seconds"]) . " " . GetTranslation("ago-" . $interval["Singular"]);
        }
    }

    return GetTranslation("ago-now");
}

function SmallString($str, $size)
{
    if (mb_strlen($str, "UTF-8") <= $size) {
        return $str;
    }

    return mb_substr($str, 0, $size - 3, "UTF-8") . "...";
}

function SendMailFromAdminTask(
    $to,
    $subject,
    $text,
    $attachments = array(),
    $embeddedImages = array(),
    $remoteAttachments = array(),
    $fromName = null
) {
    if (Config::GetConfigValue("send_mail") == "N" || IsLocalEnvironment()) {
        return true;
    }
    $message = array(
        "to" => $to,
        "subject" => $subject,
        "text" => $text,
        "attachments" => $attachments,
        "embeddedImages" => $embeddedImages,
        "remoteAttachments" => $remoteAttachments,
        "fromName" => $fromName
    );
    ErrorHandler::TriggerError(
        "RabbitMQ send_mail task triggered, to: " . $to . " subject: " . $subject,
        E_USER_NOTICE
    );

    return RabbitMQ::Send("send_mail", $message);
}

function SendMailFromAdmin(
    $to,
    $subject,
    $text,
    $attachments = array(),
    $embeddedImages = array(),
    $remoteAttachments = array(),
    $fromName = null
) {
    //send all the emails to test@trebono.de if we are in test environment
    if (IsTestEnvironment() || GetFromConfig("TestSendMail", "env") == 1) {
        $to = "test@trebono.de";
    }

    if (Config::GetConfigValue("send_mail") == "N" || IsLocalEnvironment()) {
        return true;
    }

    if (IsDemoEnvironment()) {
        $text = "DEMO " . $text;
    }

    es_include("phpmailer/phpmailer.php");

    $language =& GetLanguage();

    $phpmailer = new PHPMailer();

    $mailer = GetFromConfig("Mailer", "phpmailer");
    switch ($mailer) {
        case 'smtp':
            $phpmailer->IsSMTP();
            $phpmailer->SMTPDebug = GetFromConfig("SMTP_Debug", "phpmailer") ? true : false;
            break;
        case 'mail':
            $phpmailer->IsMail();
            break;
        case 'sendmail':
            $phpmailer->IsSendmail();
            break;
    }

    $login = GetFromConfig("SMTP_Login", "phpmailer");
    $password = GetFromConfig("SMTP_Password", "phpmailer");
    $phpmailer->Host = GetFromConfig("SMTP_Host", "phpmailer");
    $phpmailer->Port = GetFromConfig("SMTP_Port", "phpmailer");

    if ($login && $password) {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $login;
        $phpmailer->Password = $password;
    } else {
        $phpmailer->SMTPAuth = false;
    }

    $phpmailer->ContentType = "text/html";
    $phpmailer->CharSet = $language->GetHTMLCharset();

    if (is_null($fromName)) {
        $fromName = GetFromConfig("FromName");
    }

    $phpmailer->From = GetFromConfig("FromEmail");
    $phpmailer->FromName = $fromName;
    $phpmailer->AddReplyTo($phpmailer->From, $phpmailer->FromName);
    $phpmailer->Subject = $subject;
    $phpmailer->Body = $text;

    $phpmailer->AddAddress($to);

    if (is_array($attachments) && count($attachments) > 0) {
        foreach ($attachments as $v) {
            $phpmailer->AddAttachment($v);
        }
    }

    if (is_array($remoteAttachments) && count($remoteAttachments) > 0) {
        foreach ($remoteAttachments as $v) {
            if (isset($v["URL"])) {
                $phpmailer->addStringAttachment(file_get_contents($v["URL"]), $v["FileName"]);
            } elseif (isset($v["File"])) {
                $phpmailer->addStringAttachment($v["File"], $v["FileName"]);
            }
        }
    }

    if (is_array($embeddedImages) && count($embeddedImages) > 0) {
        foreach ($embeddedImages as $image) {
            $phpmailer->addEmbeddedImage($image["Path"], $image["CID"]);
        }
    }

    $result = true;

    if (!$phpmailer->Send()) {
        $result = $phpmailer->ErrorInfo;
    }
    $phpmailer->ClearAllRecipients();

    // Log message
    $fileStorage = GetFileStorage(CONTAINER__CORE);
    $fileName = date("Y-m-d-H-i-s") . "-" . uniqid();
    $filePath = PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/var/mail/" . $fileName . ".txt";

    $logMessage = "Time: " . date("d.m.Y H:i:s") . "<br/>\n";
    $logMessage .= "Status: " . ($result === true ? "success" : "failed") . "<br/>\n";
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $logMessage .= "Browser: " . $_SERVER['HTTP_USER_AGENT'] . "<br/>\n";
    }
    $logMessage .= "From: " . GetFromConfig("FromEmail") . "<br/>\n";
    $logMessage .= "From Name: " . $fromName . "<br/>\n";
    $logMessage .= "To: " . $to . "<br/>\n";
    $logMessage .= "Subject: " . $subject . "<br/>\n";
    $logMessage .= "Body: " . $text . "<br/><br/>\n\n";
    $fileStorage->PutFileContent($filePath, $logMessage, true);

    $user = new User();
    $user->LoadByEmail($to);
    if ($result === true) {
        Email::Save($user->GetProperty("user_id"), $to, $result, $subject, $fileName, "");
    } else {
        Email::Save($user->GetProperty("user_id"), $to, 0, $subject, $fileName, $result);
    }

    return $result;
}

function GetDirPrefix($langCode = DATA_LANGCODE)
{
    $language =& GetLanguage();
    $lng = $language->GetDataLanguageByCode($langCode);

    return $lng ? PROJECT_PATH . $lng['LangDir'] : PROJECT_PATH;
}

function GetUrlPrefix($langCode = DATA_LANGCODE, $withLangDir = true)
{
    $language =& GetLanguage();
    $lng = $language->GetDataLanguageByCode($langCode);
    if ($lng) {
        return $withLangDir
            ? GetFromConfig("ProtocolType") . $lng['HostName'] . PROJECT_PATH . $lng['LangDir']
            : GetFromConfig("ProtocolType") . $lng['HostName'] . PROJECT_PATH;
    }

    return GetFromConfig("ProtocolType") . $_SERVER["HTTP_HOST"] . PROJECT_PATH;
}

function GetLangDir($langCode)
{
    $language =& GetLanguage();
    $lng = $language->GetDataLanguageByCode($langCode);

    return $lng ? $lng['LangDir'] : "";
}

function Send301($newURL)
{
    $language =& GetLanguage();
    header("Content-Type: text/html; charset=" . $language->GetHTMLCharset());
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $newURL);
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>301 Moved Permanently</title>
</head><body>
<h1>Moved Permanently</h1>
<p>The document has moved <a href=\"" . $newURL . "\">here</a>.</p>
<hr>
" . $_SERVER['SERVER_SIGNATURE'] . "</body></html>";
    exit();
}

function Send403()
{
    $language =& GetLanguage();
    header("Content-Type: text/html; charset=" . $language->GetHTMLCharset());
    header("HTTP/1.1 403 Forbidden");

    $popupPage = new PopupPage();
    $content = $popupPage->Load("_403.html");
    $popupPage->Output($content);
    exit();
}

function Send404()
{
    $language =& GetLanguage();
    header("Content-Type: text/html; charset=" . $language->GetHTMLCharset());
    header("HTTP/1.1 404 Not Found");

    $customTemplate = GetFromConfig("Error404Template");

    if (strlen($customTemplate) > 0 && is_file(PROJECT_DIR . "website/" . WEBSITE_FOLDER . "/template/" . $customTemplate)) {
        $page = new LocalObject();
        $header = array("MetaTitle" => "404 Page Not Found", "Page404" => "1");

        $module = new Module();
        $moduleList = $module->GetModuleList();
        for ($i = 0; $i < count($moduleList); $i++) {
            $data = $module->LoadForHeader($moduleList[$i]["Folder"]);
            if (!is_array($data) || count($data) <= 0) {
                continue;
            }

            // Put module data to header/footer
            $header = array_merge($header, $data);
            // Put module data to content (page.html) of the static pages
            $page->AppendFromArray($data);
        }
        $publicPage = new PublicPage();
        $content = $publicPage->Load($customTemplate, $header);
        $content->LoadFromObject($page);
        $publicPage->Output($content);
        exit();
    } else {
        echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL " . htmlspecialchars($_SERVER['REQUEST_URI']) . " was not found on this server.</p>
<hr>
" . $_SERVER['SERVER_SIGNATURE'] . "</body></html>";
    }
    exit();
}

function MultiSort($array)
{
    for ($i = 1; $i < func_num_args(); $i += 3) {
        $key = func_get_arg($i);
        if (is_string($key)) {
            $key = '"' . $key . '"';
        }

        $order = true;
        if ($i + 1 < func_num_args()) {
            $order = func_get_arg($i + 1);
        }

        $type = 0;
        if ($i + 2 < func_num_args()) {
            $type = func_get_arg($i + 2);
        }
        switch ($type) {
            case 1: // Case insensitive natural.
                $t = 'strcasecmp($a[' . $key . '], $b[' . $key . '])';
                break;
            case 2: // Numeric.
                $t = '($a[' . $key . '] == $b[' . $key . ']) ? 0:(($a[' . $key . '] < $b[' . $key . ']) ? -1 : 1)';
                break;
            case 3: // Case sensitive string.
                $t = 'strcmp($a[' . $key . '], $b[' . $key . '])';
                break;
            case 4: // Case insensitive string.
                $t = 'strcasecmp($a[' . $key . '], $b[' . $key . '])';
                break;
            default: // Case sensitive natural.
                $t = 'strnatcmp($a[' . $key . '], $b[' . $key . '])';
                break;
        }
        usort($array, create_function('$a, $b', '; return ' . ($order ? '' : '-') . '(' . $t . ');'));
    }

    return $array;
}

function GetImageFields($prefix, $num)
{
    $result = array();
    for ($i = 1; $i < $num + 1; $i++) {
        $result[] = $prefix . $i;
        $result[] = $prefix . $i . "Config";
    }

    return count($result) > 0 ? implode(", ", $result) . ", " : "";
}

function PrepareContentBeforeSave($content)
{
    // Replace PROJECT_PATH by <P_T_R> (no need to update content when you move site from one folder to another)
    if (strlen($content) > 0) {
        $content = str_replace("href=\"" . PROJECT_PATH, "href=\"<P_T_R>", $content);
        $content = str_replace("href='" . PROJECT_PATH, "href='<P_T_R>", $content);
        $content = str_replace("href=" . PROJECT_PATH, "href=<P_T_R>", $content);

        $content = str_replace("src=\"" . PROJECT_PATH, "src=\"<P_T_R>", $content);
        $content = str_replace("src='" . PROJECT_PATH, "src='<P_T_R>", $content);
        $content = str_replace("src=" . PROJECT_PATH, "src=<P_T_R>", $content);

        $content = str_replace("background=\"" . PROJECT_PATH, "background=\"<P_T_R>", $content);
        $content = str_replace("background='" . PROJECT_PATH, "background='<P_T_R>", $content);
        $content = str_replace("background=" . PROJECT_PATH, "background=<P_T_R>", $content);
    }

    return $content;
}

function PrepareContentBeforeShow($content)
{
    // Replace <P_T_R> by PROJECT_PATH
    if (strlen($content) > 0) {
        $content = str_replace("<P_T_R>", PROJECT_PATH, $content);
    }

    return $content;
}

function LoadImageConfig($name, $folder, $configString)
{
    $imageConfig = explode(',', $configString);
    if (is_array($imageConfig) && count($imageConfig) > 0) {
        for ($i = 0; $i < count($imageConfig); $i++) {
            $data = explode('|', $imageConfig[$i]);
            if (!is_array($data) || count($data) <= 0) {
                continue;
            }

            if (!isset($data[2]) || strlen($data[2]) <= 0) {
                continue;
            }

            $params[$i] = array(
                'Width' => 0,
                'Height' => 0,
                'Resize' => 8,
                'Name' => $name . '_' . $data[2],
                'SourceName' => $data[2],
                'Path' => ''
            );

            $s = explode("x", $data[0]);
            if (count($s) == 2) {
                $params[$i]['Width'] = abs(intval($s[0]));
                $params[$i]['Height'] = abs(intval($s[1]));
            }

            // Resize way
            $params[$i]['Resize'] = abs(intval($data[1]));

            $cropPart = $params[$i]['Resize'] == 13 ? "_#X1#_#Y1#_#X2#_#Y2#" : "";

            $params[$i]['Path'] = PROJECT_PATH . "images/" . WEBSITE_FOLDER . "-" . $folder . "-" . $params[$i]['Width'] . "x" . $params[$i]['Height'] . $cropPart . "_" . $params[$i]['Resize'] . "/";
        }
    }

    return $params;
}

function InsertCropParams($path, $x1, $y1, $x2, $y2)
{
    $path = str_replace("#X1#", $x1, $path);
    $path = str_replace("#Y1#", $y1, $path);
    $path = str_replace("#X2#", $x2, $path);
    $path = str_replace("#Y2#", $y2, $path);

    return $path;
}

function LoadImageConfigValues($imageName, $value)
{
    $result = array();

    if (is_string($value)) {
        if (strlen($value) === 0) {
            return $result;
        }
        $value = json_decode($value, true);
    }

    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    $result[$imageName . $k . $k2] = $v2;
                }
            } else {
                $result[$imageName . $k] = $v;
            }
        }
    }

    return $result;
}

function PrepareImagePath(&$item, $key, $imageConfig, $container, $addPath = "", $keySuffix = "_image")
{
    $k = $key;
    $v = $imageConfig;

    if (isset($item[$k . $keySuffix])) {
        $imageConfigValues = isset($item[$k . $keySuffix . "_config"])
            ? LoadImageConfigValues($k . $keySuffix, $item[$k . $keySuffix . "_config"])
            : array();

        $item = array_merge($item, $imageConfigValues);

        for ($i = 0; $i < count($v); $i++) {
            if ($v[$i]["Resize"] == 13) {
                $item[$v[$i]["Name"] . "_path"] = InsertCropParams(
                    $v[$i]["Path"] . $addPath,
                    isset($item[$v[$i]["Name"] . "X1"]) ? intval($item[$v[$i]["Name"] . "X1"]) : 0,
                    isset($item[$v[$i]["Name"] . "Y1"]) ? intval($item[$v[$i]["Name"] . "Y1"]) : 0,
                    isset($item[$v[$i]["Name"] . "X2"]) ? intval($item[$v[$i]["Name"] . "X2"]) : 0,
                    isset($item[$v[$i]["Name"] . "Y2"]) ? intval($item[$v[$i]["Name"] . "Y2"]) : 0
                ) . $item[$k . $keySuffix] . "&container=" . $container;
            } else {
                $item[$v[$i]["Name"] . "_path"] = $v[$i]["Path"] . $addPath . $item[$k . $keySuffix] . "&container=" . $container;
            }

            $item[$v[$i]["Name"] . "_url"] = preg_replace(
                "/^(" . preg_quote(PROJECT_PATH, "/") . ")/",
                GetUrlPrefix(DATA_LANGCODE, false),
                $item[$v[$i]["Name"] . "_path"]
            );
        }
    }
    for ($i = 0; $i < count($v); $i++) {
        $item[$v[$i]["Name"] . "_width"] = $v[$i]["Width"];
        $item[$v[$i]["Name"] . "_height"] = $v[$i]["Height"];
    }
}

function PrepareDownloadPath(&$item, $key, $dir, $container)
{
    if (!isset($item[$key])) {
        return;
    }

    $filePath = str_replace(PROJECT_DIR, "", $dir) . $item[$key];
    ltrim($filePath, "/");
    $item[$key . "_download_path"] = PROJECT_PATH . "download/" . $filePath . "?container=" . $container;
    $item[$key . "_download_url"] = preg_replace(
        "/^(" . preg_quote(PROJECT_PATH, "/") . ")/",
        GetUrlPrefix(DATA_LANGCODE, false),
        $item[$key . "_download_path"]
    );
}

function GetRealImageSize($resize, $origW, $origH, $dstW, $dstH)
{
    if (!($origW > 0 && $origH > 0 && $dstW > 0 && $dstH > 0)) {
        return array($dstW, $dstH);
    }

    switch ($resize) {
        case RESIZE_PROPORTIONAL:
            if ($origW / $dstW > $origH / $dstH) {
                $k = $dstW / $origW;
                $dstH = round($origH * $k);
            } else {
                $k = $dstH / $origH;
                $dstW = round($origW * $k);
            }
            break;
        case RESIZE_PROPORTIONAL_FIXED_WIDTH:
            $k = $dstW / $origW;
            $dstH = round($origH * $k);
            break;
        case RESIZE_PROPORTIONAL_FIXED_HEIGHT:
            $k = $dstH / $origH;
            $dstW = round($origW * $k);
            break;
    }

    return array($dstW, $dstH);
}

function GetPriority($level)
{
    switch ($level) {
        case 1:
            $priority = 1;
            break;
        case 2:
            $priority = 0.8;
            break;
        case 3:
            $priority = 0.6;
            break;
        case 4:
            $priority = 0.4;
            break;
        default:
            $priority = 0.2;
            break;
    }

    return $priority;
}

function GetUploadMaxFileSize()
{
    $val = ini_get("upload_max_filesize");
    $val = strtolower(trim($val));
    $val = str_replace("m", " Mb", $val);
    $val = str_replace("g", " Gb", $val);
    $val = str_replace("k", " Kb", $val);

    return $val;
}


function ConvertURL2Value()
{
    $stmt = GetStatement();
    $page = new LocalObjectList();
    $page->LoadFromSQL("SELECT PageID, Config, Description FROM `page`");
    $pages = $page->GetItems();
    for ($i = 0; $i < count($pages); $i++) {
        $query = "UPDATE `page` SET Config=" . Connection::GetSQLString(value_encode(urldecode($pages[$i]['Config']))) . "
			,Description=" . Connection::GetSQLString("Description=" . value_encode(substr(
            urldecode($pages[$i]['Description']),
            12
        ))) . "
			WHERE PageID=" . $pages[$i]['PageID'];
        $stmt->Execute($query);
    }

    $catalogItem = new LocalObjectList();
    $catalogItem->LoadFromSQL("SELECT ItemID, Description FROM `catalog_item`");
    $catalogItems = $catalogItem->GetItems();
    for ($i = 0; $i < count($catalogItems); $i++) {
        $query = "UPDATE `catalog_item` SET Description=" . Connection::GetSQLString("Description=" . value_encode(substr(
            urldecode($catalogItems[$i]['Description']),
            12
        ))) . "
			WHERE ItemID=" . $catalogItems[$i]['ItemID'];
        $stmt->Execute($query);
    }
}

/**
 * array_merge_recursive2()
 *
 * Similar to array_merge_recursive but keyed-valued are always overwritten.
 * Priority goes to the 2nd array.
 *
 * @param $paArray1 array
 * @param $paArray2 array
 *
 * @return array
 *
 * @static yes
 * @public yes
 */
function array_merge_recursive2($paArray1, $paArray2)
{
    if (!is_array($paArray1) or !is_array($paArray2)) {
        return $paArray2;
    }
    foreach ($paArray2 as $sKey2 => $sValue2) {
        $paArray1[$sKey2] = array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
    }

    return $paArray1;
}

function value_encode($str)
{
    $str = str_replace("=", "%3D", $str);
    $str = str_replace("&", "%26", $str);

    return $str;
}

function value_decode($str)
{
    $str = str_replace("%3D", "=", $str);
    $str = str_replace("%26", "&", $str);

    return $str;
}

function GetValidStaticPath($staticPath, $table)
{
    $stmt = GetStatement();
    $i = 1;
    $validStaticPath = $staticPath;
    $query = "SELECT COUNT(*) FROM `" . $table . "` WHERE StaticPath=" . Connection::GetSQLString($staticPath);
    while (($result = $stmt->FetchField($query)) > 0) {
        if ($result === false || $result === null) {
            break;
        }
        $i++;
        $validStaticPath = $staticPath . "-" . $i;
        $query = "SELECT COUNT(*) FROM `" . $table . "` WHERE StaticPath=" . Connection::GetSQLString($validStaticPath);
    }

    return $validStaticPath;
}

function GetCurrentDateTime()
{
    return date("Y-m-d H:i:s");
}

function GetCurrentDate()
{
    return date("Y-m-d");
}

function GetCurrentTime()
{
    return date("H:i:s");
}

function GetDateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
{
    $dateList = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {
        $dateList[] = date($format, $current);
        $current = strtotime($step, $current);
    }

    return $dateList;
}

function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function IsTestEnvironment()
{
    //k8 environment
    if (getenv("APP_ENV") == "trebono-test") {
        return true;
    }

    //cloudfoundry environment
    if ($application = getenv("VCAP_APPLICATION")) {
        $application = json_decode($application, true);
        if (isset($application["space_name"]) && $application["space_name"] == "lst-test") {
            return true;
        }
    }

    //fallback server
    return !IsLocalEnvironment() && GetFromConfig("Environment", "env") == "test";
}

function IsDemoEnvironment()
{
    //k8 environment
    if (getenv("APP_ENV") == "trebono-demo") {
        return true;
    }

    //cloudfoundry environment
    if ($application = getenv("VCAP_APPLICATION")) {
        $application = json_decode($application, true);
        if (isset($application["space_name"]) && $application["space_name"] == "lst-demo") {
            return true;
        }
    }

    //fallback server
    return !IsLocalEnvironment() && GetFromConfig("Environment", "env") == "demo";
}

function IsReleaseEnvironment()
{
    //k8 environment
    if (getenv("APP_ENV") == "trebono") {
        return true;
    }

    //cloudfoundry environment
    if ($application = getenv("VCAP_APPLICATION")) {
        $application = json_decode($application, true);
        if (isset($application["space_name"]) && $application["space_name"] == "lst-release") {
            return true;
        }
    }

    //fallback server
    return !IsLocalEnvironment() && GetFromConfig("Environment", "env") == "production";
}

function IsLocalEnvironment()
{
    //k8 environment
    if (!empty(getenv("APP_ENV"))) {
        return false;
    }

    //cloudfoundry environment
    if ($application = getenv("VCAP_APPLICATION")) {
        $application = json_decode($application, true);
        if (isset($application["space_name"]) && $application["space_name"]) {
            return false;
        }
    }

    //fallback server
    return GetFromConfig("ForceNotLocal", "env") != 1;
}

function symm_diff($arr1, $arr2)
{
    if (gettype($arr1) != "array" || gettype($arr2) != "array") {
        return false;
    }

    return array_merge(array_diff($arr1, $arr2), array_diff($arr2, $arr1));
}

/*
 * Presents the given $date string to the specified $format.
 * Uses strtotime rules for @date
 * and  date rules for @format
 * @return string of date.
 * */
function FormatDate($format, $date)
{
    return date($format, strtotime($date));
}

/**
 * Write given message to the common api.log file
 *
 * @param $message
 * @param string $logDir
 *
 * @return string|false string of errors or false if there is no errors
 */
function ApiLog($message, $logDir = API_LOG_DIR)
{

    $fileStorage = GetFileStorage(CONTAINER__CORE);

    $content = $fileStorage->GetFileContent($logDir . "api.log");
    if (strlen($content) > 10000) {
        $fileStorage->Remove($logDir . "api.log1");
        $fileStorage->CopyFile($logDir . "api.log", $logDir . "api.log1");
        $content = "";
    }

    $date = date("Y-m-d H:i:m (P)");
    $content .= "\n[$date]: $message\n";

    $fileStorage->PutFileContent($logDir . "api.log", $content);

    return $fileStorage->HasErrors() ? $fileStorage->GetErrorsAsString() : false;
}

function SecondsToString($seconds)
{
    $dtF = new \DateTime("@0");
    $dtT = new \DateTime("@$seconds");

    $days = $dtF->diff($dtT)->format("%a");
    $daysString = GetTranslation("days");

    $hours = $dtF->diff($dtT)->format("%h");
    $hoursString = GetTranslation("hours");

    $minutes = $dtF->diff($dtT)->format("%i");
    $minutesString = GetTranslation("minutes");

    $seconds = $dtF->diff($dtT)->format("%s");
    $secondsString = GetTranslation("seconds");

    return ($days > 0 ? $days . " " . $daysString . ", " : "") .
        ($days > 0 || $hours > 0 ? $hours . " " . $hoursString . ", " : "") .
        ($days > 0 || $hours > 0 || $minutes > 0 ? $minutes . " " . $minutesString . ", " : "") .
        ($days > 0 || $hours > 0 || $minutes > 0 || $seconds > 0 ? $seconds . " " . $secondsString : "");
}

function OutputFile($filePath, $container, $fileName, $download = false, $newFileName = "")
{
    preg_match('/\.([^.]*?)$/i', $fileName, $extension);
    $extension = strtolower($extension[count($extension) - 1]);

    $contentType = "text/plain";
    switch ($extension) {
        case "pdf":
            $contentType = "application/pdf";
            break;
        case "txt":
            $contentType = "text/plain";
            break;
    }

    $fileStorage = GetFileStorage($container);

    if ($fileStorage->FileExists($filePath)) {
        if (strlen($newFileName) > 0) {
            $fileName = $newFileName;
        }
        header("Content-Type: " . $contentType);
        header("Content-disposition: " . ($download ? "attachment" : "inline") . "; filename=\"" . $fileName . "\"");
        header("Cache-Control: public, must-revalidate, max-age=0");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        echo $fileStorage->GetFileContent($filePath);
        exit();
    } else {
        Send404();
    }
}

function CleanCache($type)
{
    switch ($type) {
        case "xml":
            $path = XML_CACHE_DIR;
            break;
        case "template":
            $path = VLIB_CACHE_DIR;
            break;
        default:
            return;
    }
    if (!file_exists($path)) {
        return;
    }

    foreach (glob($path . '*') as $file) {
        $pathInfo = pathinfo($file);
        if (!isset($pathInfo['filename']) || $pathInfo['filename'] == "") {
            continue;
        }

        unlink($file);
    }
}

function GetPriceFormat($number)
{
    return number_format($number, 2, ",", ".");
}

function makeCurlFile($file)
{
    $mime = mime_content_type($file);
    $info = pathinfo($file);
    $name = $info['basename'];

    return new CURLFile($file, $mime, $name);
}

function OperationSwitch($valueOne, $valueTwo, $operation)
{
    switch ($operation) {
        case ">":
            if ($valueOne <= $valueTwo) {
                return true;
            }
            break;
        case "<":
            if ($valueOne >= $valueTwo) {
                return true;
            }
            break;
        case ">=":
            if ($valueOne < $valueTwo) {
                return true;
            }
            break;
        case "<=":
            if ($valueOne > $valueTwo) {
                return true;
            }
            break;
        default:
            if ($valueOne != $valueTwo) {
                return true;
            }
            break;
    }

    return false;
}

//this was needed for PDF/A-3 generation
function XmlContent(&$xml, $data, $attributes = array(), $namespace = "")
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (array_key_exists("attributes", $value)) {
                $attributes = $value["attributes"];
            }
            if (array_key_exists("namespace", $value)) {
                $newNamespace = $value["namespace"];
            }
            /*else
                $newNamespace = $namespace;*/

            if ($key != "attributes") {
                if (!is_numeric($key)) {
                    $subNode = $xml->addChild($key, null, $namespace);
                }
                XmlContent($subNode, $value, $attributes, $newNamespace);
                $attributes = array();
                //$namespace = $newNamespace;
            }
        } elseif ($key != "namespace") {
            $subNode = $xml->addChild($key, $value, $namespace);
            foreach ($attributes as $attrKey => $attrValue) {
                $subNode->addAttribute($attrKey, $attrValue);
            }
        }
    }
}

function FormatDateGerman($date, $languageCode)
{
    if (empty($date)) {
        return null;
    }

    $date = strtotime($date);
    $j = date("j", $date);

    if ($languageCode == "en") {
        $m = date("M", $date);
    } else {
        $m = date("m", $date);
        $subStr = $m == 3 ? 4 : 3;
        $m = substr(GetGermanMonthName($m), 0, $subStr);
    }
    $y = date("Y", $date);

    return $j . " " . $m . " " . $y;
}

function GetMonthList($startDate, $endDate, $languageCode)
{
    $date = $startDate;
    $monthList = array();
    while (strtotime($date) <= strtotime($endDate)) {
        if ($languageCode == "en") {
            $monthList[] = array("title" => date("M", strtotime($date)));
        } else {
            $m = date("m", strtotime($date)); //März gets butchered otherwise
            $subStr = $m == 3 ? 4 : 3;

            $monthList[] = array("title" => substr(GetGermanMonthName($m), 0, $subStr));
        }
        $date = date("Y-m-01", strtotime($date . " + 1 month"));
    }

    return $monthList;
}

function AdminUrl(string $path, array $data = []) {
    $baseUrl = GetFromConfig("ProtocolType") . $_SERVER["HTTP_HOST"] . ADMIN_PATH;
    $url = $baseUrl . trim($path, "/");
    $query = http_build_query($data);

    return $query ? "{$url}?{$query}" : $url;
}

function chopString($text, $maxLength, $end = "...")
{
    if (strlen($text) > $maxLength || $text == "") {
        $words = preg_split("/\s/", $text);
        $output = "";
        for ($i = 0; $i < count($words); $i++) {
            $length = strlen($output) + strlen($words[$i]);
            if ($length > $maxLength) {
                break;
            }

            $output .= " " . $words[$i];
        }
        $output .= $end;
    } else {
        $output = $text;
    }

    return $output;
}
