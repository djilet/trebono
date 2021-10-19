<?php


use Phinx\Migration\AbstractMigration;

class AgreementsFieldHistory extends AbstractMigration
{
    public function up()
    {
        $this->table('agreements_history')
        ->removeColumn("confirm_message")
        ->save();
        
        $this->table("agreements_field_history", ["id" => "value_id"])
        ->addColumn("agreement_id", "integer", ["null" => false])
        ->addColumn("property_name", "string", ["length" => 255, "null" => false])
        ->addColumn("value", "text", ["null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("user_id", "integer", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table('agreements_history')
        ->addColumn("confirm_message", "string", ["length" => 500, "null" => true])
        ->save();
        
        $this->dropTable("agreements_field_history");
    }
}
