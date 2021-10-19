<?php


use Phinx\Migration\AbstractMigration;

class BonusVoucherFileName extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
        ->addColumn("file", "string", ["length" => 255, "null" => true])
        ->save();
        
    }
    
    public function down()
    {
        $this->table("voucher")
        ->removeColumn("file")
        ->save();
    }
}
