<?php


use Phinx\Migration\AbstractMigration;

class CreateDevicesAndVersionsHistoryTable extends AbstractMigration
{
    public function up()
    {
        $this->table("device_version", ["id" => "version_id"])
            ->addColumn("device_id", "string", ["length" => 255, "null" => false])
            ->addColumn("version", "string", ["length" => 255, "null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->save();
        
        $this->table("employee")
            ->removeColumn("last_mobile_application_client")
            ->removeColumn("last_mobile_application_version")
            ->save();
    }
    
    public function down()
    {
        $this->dropTable("device_version");
        
        $this->table("employee")
            ->addColumn("last_mobile_application_client", "string", ["length" => 10, "null" => true])
            ->addColumn("last_mobile_application_version", "string", ["length" => 255, "null" => true])
            ->save();
    }
}
