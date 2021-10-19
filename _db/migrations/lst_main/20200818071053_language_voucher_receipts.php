<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherReceipts extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ReceiptDocumentDate", "Datum des Beleges");
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ReceiptDocumentDate", "Date & Time of receipt");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ReceiptDocumentDate", "Datum des Beleges");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ReceiptDocumentDate", "Datum des Beleges");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ReceiptDocumentDate", "Date & Time of receipt");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ReceiptDocumentDate", "Datum des Beleges");
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
