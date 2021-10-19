<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherVoucherErrorWithMonths extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-limit-exceeded", "Voucher amount greater than monthly limit for Benefit Voucher Service");
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-limit-exceeded", "Voucher amount greater than monthly limit for Benefit Voucher Service. Error occurred in following months: %months%");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein. In den folgenden Monaten ist ein Fehler aufgetreten: %months%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein. In den folgenden Monaten ist ein Fehler aufgetreten: %months%");
    }

    public function up()
    {
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