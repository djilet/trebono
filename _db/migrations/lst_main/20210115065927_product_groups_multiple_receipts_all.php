<?php

use Phinx\Migration\AbstractMigration;

class ProductGroupsMultipleReceiptsAll extends AbstractMigration
{
    private $productGroupList = [PRODUCT_GROUP__FOOD, PRODUCT_GROUP__BENEFIT, PRODUCT_GROUP__AD,
        PRODUCT_GROUP__RECREATION, PRODUCT_GROUP__GIFT, PRODUCT_GROUP__BONUS, PRODUCT_GROUP__BONUS_VOUCHER,
        PRODUCT_GROUP__TRANSPORT, PRODUCT_GROUP__GIVVE];
    public function up()
    {
        $this->execute("UPDATE product_group SET multiple_receipt_file='Y' WHERE receipts='Y'");
    }

    public function down()
    {
        foreach ($this->productGroupList as $code)
        {
            if(strlen($code) > 0)
                $this->execute("UPDATE product_group SET multiple_receipt_file='N' WHERE code=".Connection::GetSQLString($code));
        }
    }
}
