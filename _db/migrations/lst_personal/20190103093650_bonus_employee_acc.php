<?php


use Phinx\Migration\AbstractMigration;

class BonusEmployeeAcc extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
        ->addColumn("acc_bonus", "string", ["length" => 255, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("employee")
        ->removeColumn("acc_bonus")
        ->save();
    }
}
