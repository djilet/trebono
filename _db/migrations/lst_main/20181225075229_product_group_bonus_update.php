<?php


use Phinx\Migration\AbstractMigration;

class ProductGroupBonusUpdate extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE product_group SET receipts='Y' WHERE code='bonus'");
    }
    
    public function down()
    {
        $this->execute("UPDATE product_group SET receipts='N' WHERE code='bonus'");
    }
}
