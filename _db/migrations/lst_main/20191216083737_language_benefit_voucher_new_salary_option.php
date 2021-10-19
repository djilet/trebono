<?php

use Phinx\Migration\AbstractMigration;

class LanguageBenefitVoucherNewSalaryOption extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "V", "V");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "V", "V");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "V", "V");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "V", "V");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "V", "V");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "V", "V");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "V", "V");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "V", "V");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "V", "V");
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
