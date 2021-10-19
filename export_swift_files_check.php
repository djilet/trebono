<?php

require_once(dirname(__FILE__) . "/include/file_export_swift_to_s3.php");

$check = new FileExportSwift();

$check->CheckFiles();
