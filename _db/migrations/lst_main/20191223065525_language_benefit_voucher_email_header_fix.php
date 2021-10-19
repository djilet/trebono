<?php

use Phinx\Migration\AbstractMigration;

class LanguageBenefitVoucherEmailHeaderFix extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-header", "Ihr neuer %product_group% – als Dankeschön von %company name%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-header", "Ihr neuer %product_group% – als Dankeschön von %company name%");
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
