<?php


use Phinx\Migration\AbstractMigration;

class LanguageDatabaseName extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "database-main", "main");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "database-main", "main");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "database-main", "main");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "database-control", "control");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "database-control", "control");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "database-control", "control");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "database-personal", "personal");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "database-personal", "personal");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "database-personal", "personal");
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
