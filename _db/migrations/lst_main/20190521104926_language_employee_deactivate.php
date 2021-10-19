<?php


use Phinx\Migration\AbstractMigration;

class LanguageEmployeeDeactivate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "entity-employee", "employee");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "entity-employee", "Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "entity-employee", "Mitarbeiter");
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
