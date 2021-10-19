<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptIsNotUniqueErrorFix extends AbstractMigration
{
    private $langVarList = array();
    private $delangVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-is-not-unique", "Die Belegnummer existiert bereits, Beleg kann nicht verwendet werden. Beleg nummer: %legal_receipt_ids%");
        $this->delangVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-is-not-unique", "You should deny this receipt because receipt with the same match of receipt number + date of receipt already exists. Receipt ids are: %legal_receipt_ids%");
        $this->delangVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-is-not-unique", "Die Belegnummer existiert bereits, Beleg kann nicht verwendet werden. Beleg nummer: %legal_receipt_ids%");

        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-is-not-unique", "Die Belegnummer existiert bereits, Beleg kann nicht verwendet werden. Beleg Liste:<br/> %receipts%");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-is-not-unique", "You should deny this receipt because receipt with the same match of receipt number + date of receipt already exists. Receipts:<br/> %receipts%");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-is-not-unique", "Die Belegnummer existiert bereits, Beleg kann nicht verwendet werden. Beleg Liste:<br/> %receipts%");
    }

    public function up()
    {
        foreach($this->delangVarList as $langVar)
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
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
