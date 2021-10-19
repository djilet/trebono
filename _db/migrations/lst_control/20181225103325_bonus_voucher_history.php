<?php


use Phinx\Migration\AbstractMigration;

class BonusVoucherHistory extends AbstractMigration
{
    public function up()
    {      
        $this->table("voucher_history", ["id" => "value_id"])
        ->addColumn("voucher_id", "integer", ["null" => false])
        ->addColumn("property_name", "string", ["length" => 255, "null" => false])
        ->addColumn("value", "string", ["length" => 255,"null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("user_id", "integer", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("voucher_history");
    }
}
