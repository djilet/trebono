<?php


use Phinx\Migration\AbstractMigration;

class LanguageCronStatus extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "Status", "Status");
        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "Status", "Status");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "Status", "Status");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "StatusUpdated", "Aktualisiert");
        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "StatusUpdated", "Updated");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "StatusUpdated", "Aktualisiert");
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
