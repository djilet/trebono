<?php

use Phinx\Migration\AbstractMigration;

class LanguagePushEmployeeFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "AutochangeEmployeeSelectFilter", "Select employees by filter");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "AutochangeEmployeeSelectFilter", "Mitarbeiter nach Filter auswählen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "AutochangeEmployeeSelectFilter", "Mitarbeiter nach Filter auswählen");
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
