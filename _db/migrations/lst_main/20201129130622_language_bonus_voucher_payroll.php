<?php

use Phinx\Migration\AbstractMigration;

class LanguageBonusVoucherPayroll extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-group-bonus_voucher", "Prämien Gutschein");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-group-bonus_voucher", "Prämien Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-group-bonus_voucher", "Prämien Gutschein");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
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
    }
}
