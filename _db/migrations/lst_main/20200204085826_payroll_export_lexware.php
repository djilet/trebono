<?php

use Phinx\Migration\AbstractMigration;

class PayrollExportLexware extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadLexware", "Download Lexware");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadLexware", "Lexware herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadLexware", "Lexware herunterladen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "Lexware", "Lexware");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "Lexware", "Lexware");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "Lexware", "Lexware");
    }

    public function up()
    {
        $this->table("payroll")
            ->addColumn("lexware_file", "string", ["limit" => 255, "null" => true])
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
            ->removeColumn("lexware_file")
            ->update();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
