<?php


use Phinx\Migration\AbstractMigration;

class BonusVoucherAmountTypeFloat extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
        ->changeColumn("amount", "float")
        ->save();
        
        $this->execute("UPDATE option SET level_employee='N' WHERE code='bonus__main__salary_option'");
        
    }
    
    public function down()
    {
        $this->table("voucher")
        ->changeColumn("amount", "integer")
        ->save();
        
        $this->execute("UPDATE option SET level_employee='Y' WHERE code='bonus__main__salary_option'");
    }
}
