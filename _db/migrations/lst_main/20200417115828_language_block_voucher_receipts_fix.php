<?php

use Phinx\Migration\AbstractMigration;

class LanguageBlockVoucherReceiptsFix extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Voucher receipts");
        $this->delLangVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Voucher receipts");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein quittungen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein quittungen");
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
