<?php

require_once(dirname(__FILE__)."/../../../include/init.php");

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class AppVersionsTable extends AbstractMigration
{
    public function up()
    {
        $this->table("app_version", ["id" => "app_version_id"])
        ->addColumn("app_version", "string", ["null" => false, "length" => 255])
        ->addColumn("client", "string", ["null" => false, "length" => 10])
        ->addColumn("critical", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
        
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT DISTINCT ON(dv.version) dv.version, d.client FROM device_version AS dv LEFT JOIN device AS d ON d.device_id=dv.device_id";
        $versionList = $stmt->FetchList($query);
        
        foreach($versionList as $version)
        {
            $this->execute("INSERT INTO app_version (app_version_id, app_version, client) VALUES (nextval('\"app_version_app_version_id_seq\"'::regclass), ".Connection::GetSQLString($version["version"]).", ".Connection::GetSQLString($version["client"]).")");
        }
        
    }
    
    public function down()
    {
        $this->dropTable("app_version");
    }
}
