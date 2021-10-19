<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherValidationReccuring extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-count-exceeded", "Voucher monthly unit limit will be exceeded in %month%. Available count in this month: %count%");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die max. monatliche Anzahl Essensmarken Gutscheine für diesen Mitarbeiter im %month%. Verfügbare Anzahl: %count%");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die max. monatliche Anzahl Essensmarken Gutscheine für diesen Mitarbeiter im %month%. Verfügbare Anzahl: %count%");

        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-count-exceeded", "Voucher count greater than monthly count for Food Voucher Service. Available count: %count%");
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die monatliche Anzahl für den Food Voucher Service. Verfügbare Anzahl: %count%");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-count-exceeded", "Die Anzahl der Gutscheine ist höher als die monatliche Anzahl für den Food Voucher Service. Verfügbare Anzahl: %count%");
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
