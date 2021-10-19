<?php

use Phinx\Migration\AbstractMigration;

class LanguageFoodVoucherErrors extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-count-empty", "Count of vouchers is empty");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-count-empty", "Die Anzahl der Gutscheine ist leer");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-count-empty", "Die Anzahl der Gutscheine ist leer");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-count-is-not-number", "Count of vouchers is not number");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-count-is-not-number", "Die Anzahl der Gutscheine ist nicht die Anzahl

");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-count-is-not-number", "Die Anzahl der Gutscheine ist nicht die Anzahl

");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-count-exceeded", "Voucher count greater than monthly count for Food Voucher Service. Available count: %count%");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die monatliche Anzahl für den Food Voucher Service. Verfügbare Anzahl: %count%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die monatliche Anzahl für den Food Voucher Service. Verfügbare Anzahl: %count%");
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