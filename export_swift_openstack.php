<?php

set_time_limit(0);

require_once(dirname(__FILE__) . "/include/init.php");
require_once(dirname(__FILE__) . "/include/file_export_swift_to_openstack.php");

$export = new FileExportOpenstack();

$export->GetObjectsFromContainer();
