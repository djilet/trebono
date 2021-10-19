<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$user = new User();
$user->ValidateAccess();
//every module and its section should also revalidate access

$request = new LocalObject(array_merge($_GET, $_POST));
$moduleURL = "module.php?load=" . $request->GetProperty("load");

$adminFile = dirname(__FILE__) . "/../module/" . $request->GetProperty("load") . "/admin.php";
require_once($adminFile);
