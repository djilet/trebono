<?php

use Phinx\Migration\AbstractMigration;

class LanguageDateOfParamsError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Das Datum kann nur heute, 1. des aktuellen oder zukünftigen Monats sein.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "date-of-params-is-wrong", "Incorrect date of params for %product%. Date can only be today, 1st of current or future month.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "date-of-params-is-wrong", "Falsches Datum der Parameter für %product%. Das Datum kann nur heute, 1. des aktuellen oder zukünftigen Monats sein.");
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
