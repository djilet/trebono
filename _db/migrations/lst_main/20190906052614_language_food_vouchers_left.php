<?php

use Phinx\Migration\AbstractMigration;

class LanguageFoodVouchersLeft extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "food-voucher-left-month", "Amount of vouchers left (current month)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "food-voucher-left-month", "Anzahl verbleibender Gutscheine (aktueller Monat)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "food-voucher-left-month", "Anzahl verbleibender Gutscheine (aktueller Monat)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "food-voucher-left-year", "Amount of vouchers left (current year)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "food-voucher-left-year", "Anzahl verbleibender Gutscheine (aktuelles Jahr)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "food-voucher-left-year", "Anzahl verbleibender Gutscheine (aktuelles Jahr)");
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
