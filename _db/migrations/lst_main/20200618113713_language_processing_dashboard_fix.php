<?php

use Phinx\Migration\AbstractMigration;

class LanguageProcessingDashboardFix extends AbstractMigration
{
    private $delangVarList = array();
    private $langVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Week", "Woche");
        $this->delangVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Week", "Week");
        $this->delangVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Week", "Woche");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Calendar", "Kalender");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Calendar", "Calendar");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Calendar", "Kalender");
    }

    public function up()
    {
        foreach($this->delangVarList as $langVar)
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
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
