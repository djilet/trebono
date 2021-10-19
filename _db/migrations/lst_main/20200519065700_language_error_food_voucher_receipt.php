<?php

use Phinx\Migration\AbstractMigration;

class LanguageErrorFoodVoucherReceipt extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "no-valid-employment-contract", "Kein gültiger Arbeitsvertrag am Essen Belegdatum vorhanden");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "no-valid-employment-contract", "No valid employment contract at the receipt date anymore");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "no-valid-employment-contract", "Kein gültiger Arbeitsvertrag am Essen Belegdatum vorhanden");
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
