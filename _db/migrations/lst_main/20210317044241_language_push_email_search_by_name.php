<?php

use Phinx\Migration\AbstractMigration;

class LanguagePushEmailSearchByName extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "FilterName", "Employee Name");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "FilterName", "Vor- und/oder Nachname");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "FilterName", "Vor- und/oder Nachname");

        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "FilterCompanyUnitTitle", "Company Name");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "FilterCompanyUnitTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "FilterCompanyUnitTitle", "Unternehmen");
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
