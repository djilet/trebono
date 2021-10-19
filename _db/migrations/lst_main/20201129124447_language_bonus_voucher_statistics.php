<?php

use Phinx\Migration\AbstractMigration;

class LanguageBonusVoucherStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-statistics", "Bonus Voucher Service statistics");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-statistics", "Prämien Gutschein Statistik");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-statistics", "Prämien Gutschein Statistik");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-yearly-statistics", "Bonus Voucher Service yearly statistics");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-yearly-statistics", "Prämien Gutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BONUS_VOUCHER."-yearly-statistics", "Prämien Gutschein jährliche Statistik");
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
