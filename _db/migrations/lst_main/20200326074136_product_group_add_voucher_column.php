<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ProductGroupAddVoucherColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("product_group")
            ->addColumn("voucher", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();

        $this->execute("UPDATE product_group SET voucher='Y' WHERE code IN (".
            Connection::GetSQLString(PRODUCT_GROUP__FOOD_VOUCHER).", ".
            Connection::GetSQLString(PRODUCT_GROUP__BENEFIT_VOUCHER).", ".
            Connection::GetSQLString(PRODUCT_GROUP__GIFT).", ".
            Connection::GetSQLString(PRODUCT_GROUP__BONUS).")");
    }

    public function down()
    {
        $this->table("product_group")
            ->removeColumn("voucher")
            ->save();
    }
}
