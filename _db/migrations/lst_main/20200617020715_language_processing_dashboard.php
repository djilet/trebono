<?php

use Phinx\Migration\AbstractMigration;

class LanguageProcessingDashboard extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "GroupedBy", "Gruppiert nach");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "GroupedBy", "Grouped by");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "GroupedBy", "Gruppiert nach");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Service", "Bedienung");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Service", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Service", "Bedienung");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Employee", "Mitarbeiterin");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Employee", "Employee");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Employee", "Mitarbeiterin");
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
