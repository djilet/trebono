<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherServiceReceipts extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-voucher-not-found", "Beleg für Beleg nicht gefunden");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-voucher-not-found", "Voucher for receipt not found");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-voucher-not-found", "Beleg für Beleg nicht gefunden");

        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-voucher-not-found", "Beleg für Beleg nicht gefunden");
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-voucher-not-found", "For the chosen receipt specific category no vouchers are available anymore");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-voucher-not-found", "Beleg für Beleg nicht gefunden");
    }

    public function up()
    {
        $this->execute("INSERT INTO product_group_2_receipt_type (product_group_receipt_type_id, code, group_id) VALUES
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'shop', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)."),
                        (nextval('\"product_group_2_receipt_type_product_group_receipt_type_id_seq\"'::regclass), 'restaurant', ".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER).")");

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM product_group_2_receipt_type WHERE group_id=".ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
