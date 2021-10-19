<?php

use Phinx\Migration\AbstractMigration;

class LanguageStoredDataList extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-stored-data", "Stored Data Export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-stored-data", "Export gespeicherter Daten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-stored-data", "Export gespeicherter Daten");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-stored-data", "Stored Data Export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-stored-data", "Export gespeicherter Daten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-stored-data", "Export gespeicherter Daten");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "stored-data-status-new", "New");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "stored-data-status-new", "Neu");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "stored-data-status-new", "Neu");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "StoredData", "Stored Data Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "StoredData", "
Export gespeicherter Daten
");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "StoredData", "
Export gespeicherter Daten
");
        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Filter", "Filter");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Filter", "Auswahlkriterien");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Filter", "Auswahlkriterien");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "FilterCreatedRange", "Date & Time Range");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "FilterCreatedRange", "Datum & Zeit Bereich");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "FilterCreatedRange", "Datum & Zeit Bereich");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "FilterTitle", "Company");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "FilterTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "FilterTitle", "Unternehmen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "FilterTitlePlaceholder", "Enter title or part");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "FilterTitlePlaceholder", "Unternehmensname eingeben");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "FilterTitlePlaceholder", "Unternehmensname eingeben");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "FilterApply", "Apply");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "FilterApply", "Filter anwenden");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "FilterApply", "Filter anwenden");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "DocumentsOnPage", "Documents on page:");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "CompanyUnitTitle", "Company");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "CompanyUnitTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "CompanyUnitTitle", "Unternehmen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Created", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Period", "Period");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Period", "für Periode");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Period", "für Periode");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Status", "Status");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "Export", "Export");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "DownloadExport", "Download stored data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "DownloadExport", "Export gespeicherter Daten herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "DownloadExport", "Export gespeicherter Daten herunterladen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "RevisionHistory", "rev. history");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "RevisionHistory", "Historie");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "RevisionHistory", "Historie");

        $this->langVarList[] = new LangVar("en", "template", "billing", "stored_data_list.html", "StoredDataFor", "Stored Data for ");
        $this->langVarList[] = new LangVar("de", "template", "billing", "stored_data_list.html", "StoredDataFor", "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "stored_data_list.html", "StoredDataFor", "Gespeicherte Daten");
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