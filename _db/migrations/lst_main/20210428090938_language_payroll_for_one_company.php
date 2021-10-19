<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollForOneCompany extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "template", "billing", "payroll_list.html", "CompanyUnitID", "For Company (select one)"),
            new LangVar("de", "template", "billing", "payroll_list.html", "CompanyUnitID", "Ein Unternehmen auswählen"),
            new LangVar("tr", "template", "billing", "payroll_list.html", "CompanyUnitID", "Ein Unternehmen auswählen"),
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
