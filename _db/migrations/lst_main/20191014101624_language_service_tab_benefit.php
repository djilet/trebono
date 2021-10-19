<?php

use Phinx\Migration\AbstractMigration;

class LanguageServiceTabBenefit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_edit.html", "BenefitProgram", "Employee benefit program");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "BenefitProgram", "Employee benefit program");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
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
