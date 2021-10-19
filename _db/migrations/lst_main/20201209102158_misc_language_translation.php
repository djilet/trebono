<?php

use Phinx\Migration\AbstractMigration;

class MiscLanguageTranslation extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "product", "product_group_list.html", "MiscLanguageTranslation", "Verschiedene Nachrichten");
        $this->langVarList[] = new LangVar("en", "template", "product", "product_group_list.html", "MiscLanguageTranslation", "Miscellaneous messages");
        $this->langVarList[] = new LangVar("tr", "template", "product", "product_group_list.html", "MiscLanguageTranslation", "Verschiedene Nachrichten");

        $this->langVarList[] = new LangVar("de", "php", "product", "common", "api-error-trip-finished-description", "Fehler beim Erstellen der Quittung nach Beendigung der Reise");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "api-error-trip-finished-description", "Error on creating receipt when trip is finished");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "api-error-trip-finished-description", "Fehler beim Erstellen der Quittung nach Beendigung der Reise");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "api-error-trip-finished", "Die Reise ist bereits beendet und alle Belege abgerechnet. Sie können in dieser Reise keine Belege mehr hinzufügen. Erstellen Sie bitte eine neue Reise.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "api-error-trip-finished", "The trip has already been completed and all receipts have been processed. You can't add more receipts to this trip.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "api-error-trip-finished", "Die Reise ist bereits beendet und alle Belege abgerechnet. Sie können in dieser Reise keine Belege mehr hinzufügen. Erstellen Sie bitte eine neue Reise.");

        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "api-error-trip-finished", "Reise beendet.");
        $this->delLangVarList[] = new LangVar("en", "php", "company", "common", "api-error-trip-finished", "Trip finished.");
        $this->delLangVarList[] = new LangVar("tr", "php", "company", "common", "api-error-trip-finished", "Reise beendet.");
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
