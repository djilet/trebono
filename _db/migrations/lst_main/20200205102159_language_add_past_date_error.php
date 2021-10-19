<?php

use Phinx\Migration\AbstractMigration;

class LanguageAddPastDateError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "add-past-date-error", "Impossible add past date");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "add-past-date-error", "Vergangene Termine können nicht hinzugefügt werden");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "add-past-date-error", "Vergangene Termine können nicht hinzugefügt werden");
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
