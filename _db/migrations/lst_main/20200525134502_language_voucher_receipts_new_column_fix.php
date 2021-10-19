<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherReceiptsNewColumnFix extends AbstractMigration
{
    private $langVarList = array();
    private $deLangVarList = array();

    public function init()
    {
        $this->deLangVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Date and time of receipt");
        $this->deLangVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");
        $this->deLangVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");

        $this->deLangVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Date and time of receipt");
        $this->deLangVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");
        $this->deLangVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "DatePayment", "Date payment");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "DatePayment", "Datum der Zahlung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "DatePayment", "Datum der Zahlung");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "DatePayment", "Date payment");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "DatePayment", "Datum der Zahlung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "DatePayment", "Datum der Zahlung");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->deLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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

        foreach($this->deLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
