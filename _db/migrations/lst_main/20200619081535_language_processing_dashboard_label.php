<?php

use Phinx\Migration\AbstractMigration;

class LanguageProcessingDashboardLabel extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "DailyProcessingStatistics", "Tägliche Verarbeitungsstatistik (maximale Anzahl von Tagen - 31)");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "DailyProcessingStatistics", "Daily processing statistics (max number of days  - 31)");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "DailyProcessingStatistics", "Tägliche Verarbeitungsstatistik (maximale Anzahl von Tagen - 31)");
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
