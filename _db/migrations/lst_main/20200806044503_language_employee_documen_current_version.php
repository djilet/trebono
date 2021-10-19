<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeDocumenCurrentVersion extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "DocumentCurrentVersion", "Current version");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "DocumentCurrentVersion", "Aktuelle Versionen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "DocumentCurrentVersion", "Aktuelle Versionen");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "Show", "Show");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "Show", "Zeigen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "Show", "Zeigen");
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
