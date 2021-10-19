<?php

use Phinx\Migration\AbstractMigration;

class LanguageBatchJobBackup extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-backup", "Backup creation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-backup", "Sicherungserstellung");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-backup", "Sicherungserstellung");
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
