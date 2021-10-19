<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollExportTopas extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadTopas", "Download Topas");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadTopas", "Download Topas");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadTopas", "Download Topas");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Topas", "Topas");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Topas", "Topas");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Topas", "Topas");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatTopas", "Topas");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatTopas", "Topas");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatTopas", "Topas");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("topas_file", "string", ["limit" => 255, "null" => true])
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("payroll")
            ->removeColumn("topas_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
