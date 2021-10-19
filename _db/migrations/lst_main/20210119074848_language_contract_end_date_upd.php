<?php

use Phinx\Migration\AbstractMigration;

class LanguageContractEndDateUpd extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "contract-end-date-not-future", "Falsches Enddatum für %product%. Verträge können nur zu Enddaten zukünftiger Monate enden.");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "contract-end-date-not-future", "Falsches Enddatum für %product%. Verträge können nur zu Enddaten zukünftiger Monate enden.");
        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "contract-end-date-not-future", "Incorrect end date for %product%. Contracts can end only on end dates of future months.");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contract-end-date-not-future", "Ein Ende Datum kann nur der letzte Tag des aktuellen Monats sein oder der letzte Tag eines zukünftigen Monats.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contract-end-date-not-future", "Ein Ende Datum kann nur der letzte Tag des aktuellen Monats sein oder der letzte Tag eines zukünftigen Monats.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contract-end-date-not-future", "Incorrect end date for %product%. Contracts can end only on end dates of current or future months.");
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
