<?php

use Phinx\Migration\AbstractMigration;

class TravelNewExportRoutine extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "payroll_bookkeeping_csv", "payroll_bookkeeping_csv");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "payroll_bookkeeping_csv", "lohnbuchhaltung_csv");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "payroll_bookkeeping_csv", "lohnbuchhaltung_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "bookkeeping_export_pdf", "bookkeeping_export_pdf");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "bookkeeping_export_pdf", "bookkeeping_export_pdf");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "bookkeeping_export_pdf", "bookkeeping_export_pdf");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "CompanyUntFilter", "Company unit");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "CompanyUntFilter", "Kunde");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "CompanyUntFilter", "Kunde");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "SelectOne", "One or several companies");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "SelectOne", "Ein oder mehrere Kunde(n)");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "SelectOne", "Ein oder mehrere Kunde(n)");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "SelectAll", "All companies");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "SelectAll", "Alle Kunden");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "SelectAll", "Alle Kunden");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "AllCompanies", "All companies");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "AllCompanies", "Alle Kunden");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "AllCompanies", "Alle Kunden");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "DateOfExport", "Date of export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "DateOfExport", "Erstellungsdatum von");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "DateOfExport", "Erstellungsdatum von");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "TravelExport", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "TravelExport", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "TravelExport", "Export");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "FileName", "File name");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "FileName", "Dateiname");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "FileName", "Dateiname");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "Date", "Date");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "Date", "Datum");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "Date", "Datum");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "CreatedBy", "Created by");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "CreatedBy", "Erstellt von");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "CreatedBy", "Erstellt von");
    }

    public function up()
    {
        $this->table("payroll")
            ->removeColumn("bookkeeping_export_file")
            ->update();

        $this->table("bookkeeping_export", ["id" => "bookkeeping_export_id"])
            ->addColumn("company_unit_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("date", "date", ["null" => false])
            ->addColumn("file", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->save();

        $this->table("receipt")
            ->addColumn("bookkeeping_export_id", "string", ["length" => 255, "default" => '0'])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("payroll")
            ->addColumn("bookkeeping_export_file", "string", ["limit" => 255, "null" => true])
            ->update();

        $this->table("bookkeeping_export")
            ->drop()
            ->save();

        $this->table("receipt")
            ->removeColumn("bookkeeping_export_id")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
