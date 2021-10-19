<?php


use Phinx\Migration\AbstractMigration;

class BonusAccFlat extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
        ->renameColumn("acc_bonus", "acc_bonus_tax_flat")
        ->save();
    }
    
    public function down()
    {
        $this->table("employee")
        ->renameColumn("acc_bonus_tax_flat", "acc_bonus")
        ->save();
    }
}
