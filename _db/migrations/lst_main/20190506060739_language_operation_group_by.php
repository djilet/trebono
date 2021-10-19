<?php


use Phinx\Migration\AbstractMigration;

class LanguageOperationGroupBy extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "logging.html", "GroupBy", "Group by");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging.html", "GroupBy", "Gruppiere nach");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging.html", "GroupBy", "Gruppiere nach");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "logging-group-user", "User");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "logging-group-user", "Nutzer");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "logging-group-user", "Nutzer");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "logging-group-date", "Date");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "logging-group-date", "Datum");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "logging-group-date", "Datum");
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
