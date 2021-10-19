<?php


use Phinx\Migration\AbstractMigration;

class AgreementsMessageHistory extends AbstractMigration
{
    public function up()
    {
        $this->table('agreements_history')
        ->addColumn("confirm_message", "string", ["length" => 500, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table('agreements_history')
        ->removeColumn("confirm_message")
        ->save();
    }
}
