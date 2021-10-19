<?php

use Phinx\Migration\AbstractMigration;

class LanguageBaseModuleDeactivationReasons extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "EndEmploymentContract", "End employment contract");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "EndEmploymentContract", "Arbeitsvertrag beendet");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "EndEmploymentContract", "Arbeitsvertrag beendet");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "ContinueEmploymentContract", "Continue employment contract");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "ContinueEmploymentContract", "Arbeitsvertrag fortsetzen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "ContinueEmploymentContract", "Arbeitsvertrag fortsetzen");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "end-employment-contract", "End employment contract");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "end-employment-contract", "Arbeitsvertrag beendet");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "end-employment-contract", "Arbeitsvertrag beendet");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "continue-employment-contract", "Continue employment contract");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "continue-employment-contract", "Arbeitsvertrag fortsetzen");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "continue-employment-contract", "Arbeitsvertrag fortsetzen");
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
