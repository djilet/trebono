<?php


use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardSwift extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "SwiftStorageStatistics", "Swift size statistics");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "SwiftStorageStatistics", "Größenstatistik Swift");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "SwiftStorageStatistics", "Größenstatistik Swift");

        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DatabaseStatistics", "Database size statistics");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DatabaseStatistics", "Datenbankgrößenstatistik");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DatabaseStatistics", "Datenbankgrößenstatistik");

        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DatabaseName", "Database name");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DatabaseName", "Name der Datenbank");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DatabaseName", "Name der Datenbank");

        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "StorageName", "Container name");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "StorageName", "Containername");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "StorageName", "Containername");

        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "SpaceUsed", "Size");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "SpaceUsed", "Größe");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "SpaceUsed", "Größe");

        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "ObjectCount", "Number of Files");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "ObjectCount", "Anzahl der Dateien");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "ObjectCount", "Anzahl der Dateien");
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
