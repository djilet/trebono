<?php

use Phinx\Migration\AbstractMigration;

class LanguageContractEndDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contract-end-date-not-future", "Falsches Enddatum für %product%. Verträge können nur zu Enddaten zukünftiger Monate enden.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contract-end-date-not-future", "Falsches Enddatum für %product%. Verträge können nur zu Enddaten zukünftiger Monate enden.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contract-end-date-not-future", "Incorrect end date for %product%. Contracts can end only on end dates of future months.");
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
