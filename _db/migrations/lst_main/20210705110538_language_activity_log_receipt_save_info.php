<?php

use Phinx\Migration\AbstractMigration;

class LanguageActivityLogReceiptSaveInfo extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-receipt_id_update_next_receipt", "Aktualisieren Beleg (Nächster Beleg Taste) Nr.");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-receipt_id_update_next_receipt", "Aktualisieren Beleg (Nächster Beleg Taste) Nr.");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-receipt_id_update_next_receipt", "Update the receipt (Next receipt button)");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-receipt_id_update_save", "Aktualisieren Beleg (Speichern Taste) Nr.");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-receipt_id_update_save", "Aktualisieren Beleg (Speichern Taste) Nr.");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-receipt_id_update_save", "Update the receipt (Save button)");
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
