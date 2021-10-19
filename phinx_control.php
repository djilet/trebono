<?php
require_once(dirname(__FILE__) . "/include/init.php");

$credentials = GetDatabaseCredentials(DB_CONTROL);

$dsn = "pgsql:host=" . $credentials["Host"] . ";port=5432;dbname=" . $credentials["Database"] . ";user=" . $credentials["User"] . ";password=" . $credentials["Password"];
$pdo = new PDO($dsn);

return array(
    "paths" => array(
        "migrations" => dirname(__FILE__) . "/_db/migrations/lst_control"
    ),
    "environments" => array(
        "default_migration_table" => "_migration",
        "default_database" => "",
        "the_only" => array(
            "name" => $credentials["Database"],
            "connection" => $pdo
        )
    ),
    "version_order" => "creation"
);