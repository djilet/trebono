<?php


use Phinx\Migration\AbstractMigration;

class VoucherLogoImage extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")
        ->addColumn("voucher_logo_image", "string", ['limit' => 70, "null" => true])
        ->save();
        
    }
    
    public function down()
    {
        $this->table("company_unit")
        ->removeColumn("voucher_logo_image")
        ->save();
    }
}
