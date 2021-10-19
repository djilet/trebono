<?php


use Phinx\Migration\AbstractMigration;

class SaveVersionIdForReceipt extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt")
        ->addColumn("version_id", "integer", ["null" => true])
        ->removeColumn("device_id")
        ->save();
    }
    
    public function down()
    {
        $this->table("receipt")
        ->removeColumn("version_id")
        ->addColumn("device_id", "string", ["length" => 255, "null" => true])
        ->save();
    }
}
