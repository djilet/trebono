<?php

use Phinx\Migration\AbstractMigration;

class LanguageCronOperationCheckWorkers extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-check_workers", "Check workers");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-check_workers", "Überprüfen Sie die Arbeiter");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-check_workers", "Überprüfen Sie die Arbeiter");
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
