<?php

set_time_limit(0);

require_once(dirname(__FILE__) . "/include/file_export_swift_to_s3.php");

$export = new FileExportSwift();

$export->GetObjectsFromContainer();
