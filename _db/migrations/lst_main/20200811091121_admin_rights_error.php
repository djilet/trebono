<?php

use Phinx\Migration\AbstractMigration;

class AdminRightsError extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "product-full-title-benefit_voucher__main", "Benefit Voucher Service");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "product-full-title-benefit_voucher__main", "Sachbezug Gutschein Service");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "product-full-title-benefit_voucher__main", "Sachbezug Gutschein Service");

        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "product-full-title-food_voucher__main", "Food Voucher Service");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "product-full-title-food_voucher__main", "Essensmarken Gutschein Service");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "product-full-title-food_voucher__main", "Essensmarken Gutschein Service");

        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "voucher-start-date-not-1st", "Der BVS-Dienst kann erst zum 1. eines zukünftigen Monats beginnen.");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "voucher-start-date-not-1st", "Der BVS-Dienst kann erst zum 1. eines zukünftigen Monats beginnen.");
        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "voucher-start-date-not-1st", "BVS service can start only at a 1st of a future month.");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-start-date-not-1st", "Falsches Startdatum für %product%. Dieser Service kann erst am 1. des aktuellen oder zukünftigen Monats gestartet werden.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-start-date-not-1st", "Falsches Startdatum für %product%. Dieser Service kann erst am 1. des aktuellen oder zukünftigen Monats gestartet werden.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-start-date-not-1st", "Incorrect start date for %product%. This service can start only at 1st of current or future month.");
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
