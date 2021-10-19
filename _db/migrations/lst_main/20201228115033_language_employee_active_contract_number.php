<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeActiveContractNumber extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "ActiveContractNumber", "Aktive Arbeitsvertragsnummer");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "ActiveContractNumber", "Active contract number");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "ActiveContractNumber", "Aktive Arbeitsvertragsnummer");
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
