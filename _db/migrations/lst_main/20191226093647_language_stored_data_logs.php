<?php

use Phinx\Migration\AbstractMigration;

class LanguageStoredDataLogs extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-stored_data_create", "Stored data creation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-stored_data_create", "Erstellung gespeicherter Daten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-stored_data_create", "Erstellung gespeicherter Daten");
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
