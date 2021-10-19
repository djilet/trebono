<?php

use Phinx\Migration\AbstractMigration;

class LanguageMasterDataList extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-master-data-export", "Master Data Export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-master-data-export", "Stammdatenexport");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-master-data-export", "Stammdatenexport");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-master-data", "Master Data Export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-master-data", "Stammdatenexport");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-master-data", "Stammdatenexport");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "MasterData", "Master Data Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "MasterData", "
Stammdatenexport
");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "MasterData", "
Stammdatenexport
");
        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "GenerateMasterData", "Generate Master data");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "GenerateMasterData", "Stammdaten generieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "GenerateMasterData", "Stammdaten generieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "MasterDataType", "Master data type");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "MasterDataType", "Stammdatentyp");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "MasterDataType", "Stammdatentyp");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "EmployeeMasterData", "Employee Master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "EmployeeMasterData", "Export von Mitarbeiterstammdaten");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "EmployeeMasterData", "Export von Mitarbeiterstammdaten");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyMasterData", "Company Master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyMasterData", "Export von Firmenstammdaten");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyMasterData", "Export von Firmenstammdaten");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "DocumentsOnPage", "Documents on page:");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "Created", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "Export", "Export");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "DownloadExport", "Download master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "DownloadExport", "Export gespeicherter Daten herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "DownloadExport", "Export gespeicherter Daten herunterladen");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "master-data-no-employees", "No new customers or employees since the last master data export");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "master-data-no-employees", "Keine neuen Kunden oder Mitarbeiter seit dem letzten Stammdaten Export");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "master-data-no-employees", "Keine neuen Kunden oder Mitarbeiter seit dem letzten Stammdaten Export");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "master-data-success", "Generation completed successfully");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "master-data-success", "Generierung erfolgreich abgeschlossen");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "master-data-success", "Generierung erfolgreich abgeschlossen");
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
