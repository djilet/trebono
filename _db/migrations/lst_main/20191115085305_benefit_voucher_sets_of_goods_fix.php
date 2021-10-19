<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherSetsOfGoodsFix extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-voucher_sets_of_goods", "Benefit Voucher category list");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-voucher_sets_of_goods", "Benefit Voucher Kategorieliste");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-voucher_sets_of_goods", "Benefit Voucher Kategorieliste");

        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher category list");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher Kategorieliste");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "config-voucher_sets_of_good", "Benefit Voucher Kategorieliste");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
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
