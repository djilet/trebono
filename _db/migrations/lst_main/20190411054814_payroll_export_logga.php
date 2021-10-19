<?php


use Phinx\Migration\AbstractMigration;

class PayrollExportLogga extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadLogga", "Download LOGGA");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadLogga", "Download LOGGA");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadLogga", "Download LOGGA");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Logga", "LOGGA");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Logga", "LOGGA");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Logga", "LOGGA");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "DatevFormatLogga", "LOGGA");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "DatevFormatLogga", "LOGGA");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "DatevFormatLogga", "LOGGA");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("logga_file", "string", ["limit" => 255, "null" => true])
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
            ->removeColumn("logga_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
