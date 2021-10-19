<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherReceiptsNewColumn extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Date and time of receipt");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Date and time of receipt");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "DateTimeReceipt", "Datum und Uhrzeit der eingereichten Rechnung");
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
