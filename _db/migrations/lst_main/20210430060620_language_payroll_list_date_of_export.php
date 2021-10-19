<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollListDateOfExport extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "template", "billing", "payroll_list.html", "DateOfExport", "Date of Export"),
            new LangVar("de", "template", "billing", "payroll_list.html", "DateOfExport", "Erstellungsdatum von"),
            new LangVar("tr", "template", "billing", "payroll_list.html", "DateOfExport", "Erstellungsdatum von"),
        ];
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
