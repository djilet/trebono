<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeDefaultValues extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "SalutationMr", "Mr.");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "SalutationMr", "Herr");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "SalutationMr", "Herr");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "SalutationMs", "Ms.");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "SalutationMs", "Frau");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "SalutationMs", "Frau");

        $this->langVarList[] = new LangVar("en", "template", "company", "contact_edit.html", "SalutationMr", "Mr.");
        $this->langVarList[] = new LangVar("de", "template", "company", "contact_edit.html", "SalutationMr", "Herr");
        $this->langVarList[] = new LangVar("tr", "template", "company", "contact_edit.html", "SalutationMr", "Herr");

        $this->langVarList[] = new LangVar("en", "template", "company", "contact_edit.html", "SalutationMs", "Ms.");
        $this->langVarList[] = new LangVar("de", "template", "company", "contact_edit.html", "SalutationMs", "Frau");
        $this->langVarList[] = new LangVar("tr", "template", "company", "contact_edit.html", "SalutationMs", "Frau");

        $this->langVarList[] = new LangVar("en", "template", "core", "user_edit.html", "SalutationMr", "Mr.");
        $this->langVarList[] = new LangVar("de", "template", "core", "user_edit.html", "SalutationMr", "Herr");
        $this->langVarList[] = new LangVar("tr", "template", "core", "user_edit.html", "SalutationMr", "Herr");

        $this->langVarList[] = new LangVar("en", "template", "core", "user_edit.html", "SalutationMs", "Ms.");
        $this->langVarList[] = new LangVar("de", "template", "core", "user_edit.html", "SalutationMs", "Frau");
        $this->langVarList[] = new LangVar("tr", "template", "core", "user_edit.html", "SalutationMs", "Frau");

        $this->langVarList[] = new LangVar("en", "template", "partner", "contact_edit.html", "SalutationMr", "Mr.");
        $this->langVarList[] = new LangVar("de", "template", "partner", "contact_edit.html", "SalutationMr", "Herr");
        $this->langVarList[] = new LangVar("tr", "template", "partner", "contact_edit.html", "SalutationMr", "Herr");

        $this->langVarList[] = new LangVar("en", "template", "partner", "contact_edit.html", "SalutationMs", "Ms.");
        $this->langVarList[] = new LangVar("de", "template", "partner", "contact_edit.html", "SalutationMs", "Frau");
        $this->langVarList[] = new LangVar("tr", "template", "partner", "contact_edit.html", "SalutationMs", "Frau");
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
