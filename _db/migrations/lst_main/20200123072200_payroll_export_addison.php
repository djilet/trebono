<?php

use Phinx\Migration\AbstractMigration;

class PayrollExportAddison extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadAddison", "Download Addison");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadAddison", "Addison herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadAddison", "Addison herunterladen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Addison", "Addison");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Addison", "Addison");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Addison", "Addison");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("addison_file", "string", ["limit" => 255, "null" => true])
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
            ->removeColumn("addison_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
