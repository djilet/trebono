<?php

use Phinx\Migration\AbstractMigration;

class ProcessingDashboard extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-processing_dashboard", "Quittungsverarbeitung Dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-processing_dashboard", "Processing Dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-processing_dashboard", "Quittungsverarbeitung Dashboard");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-processing_dashboard", "Quittungsverarbeitung Dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-processing_dashboard", "Processing Dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-processing_dashboard", "Quittungsverarbeitung Dashboard");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "ProcessingDashboard", "Quittungsverarbeitung Dashboard");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "ProcessingDashboard", "Processing Dashboard");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "ProcessingDashboard", "Quittungsverarbeitung Dashboard");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "YearlyProcessingStatistics", "Wird bearbeitet jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "YearlyProcessingStatistics", "Yearly processing statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "YearlyProcessingStatistics", "Wird bearbeitet jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "ProcessingStatisticsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "ProcessingStatisticsShow", "Show");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "ProcessingStatisticsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "ServiceName", "Dienstname");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "ServiceName", "Service name");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "ServiceName", "Dienstname");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "StatisticsTotal", "Gesamt");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "StatisticsTotal", "Total");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "StatisticsTotal", "Gesamt");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "View", "Aussicht");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "View", "View");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "View", "Aussicht");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Month", "Monat");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Month", "Month");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Month", "Monat");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Week", "Woche");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Week", "Week");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Week", "Woche");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Expand", "Details");

        $this->langVarList[] = new LangVar("de", "template", "core", "processing_dashboard.html", "Collapse", "Einklappen");
        $this->langVarList[] = new LangVar("en", "template", "core", "processing_dashboard.html", "Collapse", "Collapse");
        $this->langVarList[] = new LangVar("tr", "template", "core", "processing_dashboard.html", "Collapse", "Einklappen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "amount_processing", "Gesamt Euro-Wert");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "amount_processing", "Total euro value");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "amount_processing", "Gesamt Euro-Wert");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "time_processing", "Gesamtzeit");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "time_processing", "Total time");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "time_processing", "Gesamtzeit");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "count_processing", "Gesamteingänge");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "count_processing", "Total receipts");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "count_processing", "Gesamteingänge");
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
