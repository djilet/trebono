<?php


use Phinx\Migration\AbstractMigration;

class ReceiptAddIndex extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt")
            ->addIndex("employee_id")
            ->addIndex("group_id")
            ->save();
    }
    
    public function down()
    {
        $this->table("receipt")
        ->removeIndex(["group_id"])
        ->removeIndex(["employee_id"])
        ->save();
    }
}
