<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptValidationError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "employee-has-interruption-contract", "Employee has active interruption contract");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "employee-has-interruption-contract", "Mitarbeiter hat aktiven Unterbrechungsvertrag");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "employee-has-interruption-contract", "Mitarbeiter hat aktiven Unterbrechungsvertrag");
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
