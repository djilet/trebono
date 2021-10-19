<?php

use Phinx\Migration\AbstractMigration;

class RecreationRemoveDocumentation extends AbstractMigration
{
    public function up()
    {
        $this->execute("DELETE FROM product_group_2_receipt_type WHERE (code='doc' AND group_id=".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION).")
        OR code='confirm' AND group_id=".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION));
    }

    public function down()
    {
        $this->execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, code, group_id) VALUES
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'doc', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'confirm', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION).")");
    }
}
