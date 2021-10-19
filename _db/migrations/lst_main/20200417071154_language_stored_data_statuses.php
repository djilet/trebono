<?php

use Phinx\Migration\AbstractMigration;

class LanguageStoredDataStatuses extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "stored-data-status-sent", "Sent");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "stored-data-status-sent", "Geschickt");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "stored-data-status-sent", "Geschickt");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "stored-data-status-error", "Error");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "stored-data-status-error", "Error");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "stored-data-status-error", "Error");
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
