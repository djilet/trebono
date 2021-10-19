<?php

use Phinx\Migration\AbstractMigration;

class PayrollExportPerforce extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadPerforce", "Download Perforce");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadPerforce", "Download Perforce");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadPerforce", "Download Perforce");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Perforce", "Perforce");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Perforce", "Perforce");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Perforce", "Perforce");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatPerforce", "Perforce");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatPerforce", "Perforce");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatPerforce", "Perforce");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_perforce_csv", "payroll_perforce_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_perforce_csv", "payroll_perforce_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_perforce_csv", "payroll_perforce_csv");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("perforce_file", "string", ["limit" => 255, "null" => true])
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
            ->removeColumn("perforce_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
