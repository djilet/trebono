<?php

use Phinx\Migration\AbstractMigration;

class PayrollExportSage extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadSage", "Download SAGE");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadSage", "Download SAGE");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadSage", "Download SAGE");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Sage", "SAGE");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Sage", "SAGE");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Sage", "SAGE");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatSage", "SAGE");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatSage", "SAGE");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatSage", "SAGE");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("sage_file", "string", ["limit" => 255, "null" => true])
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
            ->removeColumn("sage_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
