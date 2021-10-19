<?php


use Phinx\Migration\AbstractMigration;

class BonusCompanyUnitAccs extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")
        ->addColumn("acc_bonus", "string", ["length" => 255, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("company_unit")
        ->removeColumn("acc_bonus")
        ->save();
    }
}
