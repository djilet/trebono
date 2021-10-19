<?php

use Phinx\Migration\AbstractMigration;

class ProductGroupsMultipleReceiptsUpdate extends AbstractMigration
{
    private $productGroupList = [PRODUCT_GROUP__FOOD_VOUCHER, PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__GIFT_VOUCHER,
        PRODUCT_GROUP__INTERNET, PRODUCT_GROUP__MOBILE];
    public function up()
    {
        foreach ($this->productGroupList as $code)
        {
            $this->execute("UPDATE product_group SET multiple_receipt_file='Y' WHERE code=".Connection::GetSQLString($code));
        }
    }

    public function down()
    {
        foreach ($this->productGroupList as $code)
        {
            $this->execute("UPDATE product_group SET multiple_receipt_file='N' WHERE code=".Connection::GetSQLString($code));
        }
    }
}
