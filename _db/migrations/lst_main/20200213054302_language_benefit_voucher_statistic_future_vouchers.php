<?php

use Phinx\Migration\AbstractMigration;

class LanguageBenefitVoucherStatisticFutureVouchers extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "FutureGenerated", "noch nicht generiert");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "FutureGenerated", "not generated yet");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "FutureGenerated", "noch nicht generiert");
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
