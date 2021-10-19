<?php

use Phinx\Migration\AbstractMigration;

class LanguageInterruptionStartDateNotFuture extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "interruption-start-date-not-future", "Falsches Startdatum für %product%. Dieser Dienst kann nur am aktuellen oder zukünftigen Datum starten.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "interruption-start-date-not-future", "Falsches Startdatum für %product%. Dieser Dienst kann nur am aktuellen oder zukünftigen Datum starten.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "interruption-start-date-not-future", "Incorrect start date for %product%. This service can start only at current or future date.");
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
