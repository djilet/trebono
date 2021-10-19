<?php

use Phinx\Migration\AbstractMigration;

class LanguageDateOfParamsErrorFix2 extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Datum darf nicht kleiner als 1. Tag des aktuellen Monats sein.");
        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "date-of-params-is-wrong", "Incorrect date of params for %product%. Date cannot be less than 1st day of current month.");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Datum darf nicht kleiner als 1. Tag des aktuellen Monats sein.");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Datum kann nur der 1. Tag des aktuellen oder nächsten Monats sein.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "date-of-params-is-wrong", "Incorrect date of params for %product%. Date can only be the 1st day of the current or next months.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Datum kann nur der 1. Tag des aktuellen oder nächsten Monats sein.");
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
