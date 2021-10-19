<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollResetEmptyDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "PayrollResetEmptyDate", "Please, select date for payroll");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "PayrollResetEmptyDate", "Bitte wählen Sie ein Datum für die Abrechnung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "PayrollResetEmptyDate", "Bitte wählen Sie ein Datum für die Abrechnung");
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
