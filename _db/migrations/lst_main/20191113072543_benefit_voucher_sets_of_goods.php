<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherSetsOfGoods extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "SetOfGoods", "Category");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "SetOfGoods", "Kategorie");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "SetOfGoods", "Kategorie");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher category list");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher Kategorieliste");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher Kategorieliste");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'voucher_sets_of_goods',
							'Car related goods
Health Goods',
							'misc'::character varying,
							'plain'::character varying,
							NOW()
						)");

        $this->table("receipt")
            ->addColumn("sets_of_goods", "text", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='voucher_sets_of_goods'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
