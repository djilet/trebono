<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherPaymentStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "VoucherOpenAmount", "Offener Betrag");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "VoucherOpenAmount", "Open amount");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "VoucherOpenAmount", "Offener Betrag");
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
