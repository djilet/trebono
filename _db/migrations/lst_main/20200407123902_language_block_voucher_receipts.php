<?php

use Phinx\Migration\AbstractMigration;

class LanguageBlockVoucherReceipts extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ReceiptReceiptID", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ReceiptReceiptID", "Beleg ID");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ReceiptReceiptID", "Beleg ID");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ReceiptUpdated", "Last Update");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ReceiptUpdated", "Letze Aktualisierung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ReceiptUpdated", "Letze Aktualisierung");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ReceiptCreated", "Created");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ReceiptCreated", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ReceiptCreated", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "Service", "Service");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "Service", "Modul");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "Service", "Modul");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ReceiptStatus", "Status");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ReceiptStatus", "Aktueller Status");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ReceiptStatus", "Aktueller Status");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Voucher receipts");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Voucher receipts");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Voucher receipts");
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
