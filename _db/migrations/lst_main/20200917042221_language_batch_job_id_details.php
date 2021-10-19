<?php

use Phinx\Migration\AbstractMigration;

class LanguageBatchJobIdDetails extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "ExpandEmployeeList", "Expand employee list");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "ExpandEmployeeList", "Erweitern Sie die Mitarbeiterliste");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "ExpandEmployeeList", "Erweitern Sie die Mitarbeiterliste");

        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "CollapseEmployeeList", "Collapse employee list");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "CollapseEmployeeList", "Mitarbeiterliste reduzieren");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "CollapseEmployeeList", "Mitarbeiterliste reduzieren");
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
