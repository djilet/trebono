<?php


use Phinx\Migration\AbstractMigration;

class SaveReasonOfDeniedToReceipt extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt")
        ->addColumn("denial_reason", "text", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("receipt")
        ->removeColumn("denial_reason")
        ->save();
    }
}
