<?php

use Phinx\Migration\AbstractMigration;

class LanguageServiceTabBenefitFix extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "template", "company", "company_edit.html", "BenefitProgram", "Employee benefit program");
        $this->delLangVarList[] = new LangVar("de", "template", "company", "company_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "company_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitProgram", "Employee benefit program");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitProgram", "Mitarbeiter Bonus Program");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
