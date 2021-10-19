<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class NewYearEmployeeReport extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "YearlyTotalBenefits", "Yearly total benefits");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "YearlyTotalBenefits", "Jährlicher Gesamtnutzen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "YearlyTotalBenefits", "Jährlicher Gesamtnutzen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "TabReports", "Reports");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "TabReports", "Berichte");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "TabReports", "Berichte");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CreateReport", "Create report");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CreateReport", "Bericht erstellen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CreateReport", "Bericht erstellen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "YearlyReportDate", "Report year");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "YearlyReportDate", "Berichtsjahr");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "YearlyReportDate", "Berichtsjahr");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "YearlyReport", "Create report");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "YearlyReport", "Bericht erstellen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "YearlyReport", "Bericht erstellen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Reports", "Reports");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Reports", "Berichte");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Reports", "Berichte");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "ReportTitle", "Report");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "ReportTitle", "Bericht");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "ReportTitle", "Bericht");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CreatedBy", "Created by");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CreatedBy", "Erstellt von");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CreatedBy", "Erstellt von");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Created", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "ReportPeriod", "Period");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "ReportPeriod", "Berichtszeitraum");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "ReportPeriod", "Berichtszeitraum");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DownloadReport", "Download report");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DownloadReport", "Bericht herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DownloadReport", "Bericht herunterladen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-yearly_report_generate", "Jahresbericht erstellen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-yearly_report_generate", "Generate yearly report");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-yearly_report_generate", "Jahresbericht erstellen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-yearly_report_download", "Bericht herunterladen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-yearly_report_download", "Download report");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-yearly_report_download", "Bericht herunterladen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-yearly_report_remove", "Bericht deaktivieren");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-yearly_report_remove", "Deactivate report");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-yearly_report_remove", "Bericht deaktivieren");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-yearly_report_activate", "Bericht aktivieren");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-yearly_report_activate", "Activate report");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-yearly_report_activate", "Bericht aktivieren");
    }

    public function up()
    {
        $this->table("yearly_employee_report", ["id" => "report_id"])
            ->addColumn("company_unit_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("date_from", "date", ["null" => false])
            ->addColumn("date_to", "date", ["null" => false])
            ->addColumn("file", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("yearly_employee_report")
            ->drop()
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
