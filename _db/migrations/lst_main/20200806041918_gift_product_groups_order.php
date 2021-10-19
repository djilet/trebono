<?php

use Phinx\Migration\AbstractMigration;

class GiftProductGroupsOrder extends AbstractMigration
{
    public function up()
    {
        $giftProductGroupOrder = $this->fetchRow("SELECT sort_order FROM product_group
            WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT));
        $gvsProductGroupOrder = $this->fetchRow("SELECT sort_order FROM product_group
            WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));

        $this->execute("UPDATE product_group SET sort_order=".$gvsProductGroupOrder["sort_order"]."
                WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT));
        $this->execute("UPDATE product_group SET sort_order=".$giftProductGroupOrder["sort_order"]."
                WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));
    }

    public function down()
    {
        $giftProductGroupOrder = $this->fetchRow("SELECT sort_order FROM product_group
            WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT));
        $gvsProductGroupOrder = $this->fetchRow("SELECT sort_order FROM product_group
            WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));

        $this->execute("UPDATE product_group SET sort_order=".$giftProductGroupOrder["sort_order"]."
                WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT));
        $this->execute("UPDATE product_group SET sort_order=".$gvsProductGroupOrder["sort_order"]."
                WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));
    }
}
