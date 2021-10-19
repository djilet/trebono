<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptDeleteButton extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button", "Quittung löschen");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button", "Delete receipt");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button", "Quittung löschen");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang löschen");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button-description", "Title of the delete receipt button");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang löschen");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question", "Möchten Sie die Quittung wirklich löschen?");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question", "Are you sure you want to delete the receipt?");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question", "Möchten Sie die Quittung wirklich löschen?");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Löschung");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question-description", "Confirmation of receipt deletion");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Löschung");
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
