<?php

use Phinx\Migration\AbstractMigration;

class LanguageBookkeepingExport extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExport", "Bookkeeping Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExport", "Buchhaltungsexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExport", "Buchhaltungsexport");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-bookkeeping-export", "Bookkeeping export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-bookkeeping-export", "Buchhaltungsexport");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-bookkeeping-export", "Buchhaltungsexport");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-bookkeeping-export", "Bookkeeping export");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-bookkeeping-export", "Buchhaltungsexport");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-bookkeeping-export", "Buchhaltungsexport");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "permission-bookkeeping_export", "Bookkeeping receiver");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-bookkeeping_export", "Buchhaltungsempfänger");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-bookkeeping_export", "Buchhaltungsempfänger");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Filter", "Filter");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Filter", "Auswahlkriterien");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Filter", "Auswahlkriterien");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "FilterCreatedRange", "Date & Time Range");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "FilterCreatedRange", "Datum & Zeit Bereich");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "FilterCreatedRange", "Datum & Zeit Bereich");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "FilterTitle", "Company");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "FilterTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "FilterTitle", "Unternehmen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "FilterTitlePlaceholder", "Enter title or part");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "FilterTitlePlaceholder", "Unternehmensname eingeben");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "FilterTitlePlaceholder", "Unternehmensname eingeben");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "FilterApply", "Apply");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "FilterApply", "Filter anwenden");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "FilterApply", "Filter anwenden");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "DocumentsOnPage", "Documents on page:");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "CompanyUnitTitle", "Company");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "CompanyUnitTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "CompanyUnitTitle", "Unternehmen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Created", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Period", "Period");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Period", "für Periode");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Period", "für Periode");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Status", "Status");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Export", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Export", "Export");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "DownloadExport", "Download bookkeeping export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "DownloadExport", "Laden Sie den Buchhaltungsexport herunter");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "DownloadExport", "Laden Sie den Buchhaltungsexport herunter");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("bookkeeping_export_file", "string", ["limit" => 255, "null" => true])
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->table("company_unit")
            ->renameColumn("acc_corporate_hospitality", "acc_hospitality")
            ->renameColumn("acc_other_costs", "acc_other")
            ->save();
    }

    public function down()
    {
        $this->table("payroll")
            ->removeColumn("bookkeeping_export_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $this->table("company_unit")
            ->renameColumn("acc_hospitality", "acc_corporate_hospitality")
            ->renameColumn("acc_other", "acc_other_costs")
            ->save();
    }
}
