<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptEmptyDocumentDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-empty-document-guid", "Receipt number must be filled.");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-empty-document-guid", "Die Rechnungsnr. muss ausgefüllt sein");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-empty-document-guid", "Die Rechnungsnr. muss ausgefüllt sein");
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
