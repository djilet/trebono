<?php

use Phinx\Migration\AbstractMigration;

class LanguageDeniedReceiptApi extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button", "Quittung löschen");
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button", "Delete receipt");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button", "Quittung löschen");

        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang löschen");
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button-description", "Title of the delete receipt button");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang löschen");

        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question", "Möchten Sie die Quittung wirklich löschen?");
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question", "Are you sure you want to delete the receipt?");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question", "Möchten Sie die Quittung wirklich löschen?");

        $this->delLangVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Löschung");
        $this->delLangVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question-description", "Confirmation of receipt deletion");
        $this->delLangVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Löschung");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button", "Verwenden Sie keine Quittung");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button", "Don't use receipt");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button", "Verwenden Sie keine Quittung");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang verweigert");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-button-description", "Title of the denied receipt button");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-button-description", "Titel der Schaltfläche Empfang verweigert");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question", "Wollen Sie die Quittung wirklich verweigern?");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question", "Are you sure you want to deny the receipt?");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question", "Wollen Sie die Quittung wirklich verweigern?");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Verweigerung");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "api-delete-receipt-question-description", "Confirmation of receipt denial");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "api-delete-receipt-question-description", "Bestätigung des Eingangs Verweigerung");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "denied-receipt-chat-message", "Nicht verwenden, Danke");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "denied-receipt-chat-message", "Do not use, Thank you");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "denied-receipt-chat-message", "Nicht verwenden, Danke");
    }

    public function up()
    {
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

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

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
