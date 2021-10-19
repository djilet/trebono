<?php


use Phinx\Migration\AbstractMigration;

class BonusVoucherFrequency extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
        ->addColumn("recurring_frequency", "string", ["length" => 10,"null" => true])
        ->save();
        
    }
    
    public function down()
    {
        $this->table("voucher")
        ->removeColumn("recurring_frequency")
        ->save();
    }
}
