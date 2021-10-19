<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherReceiptsFix extends AbstractMigration
{
    private $langVarList = array();
    private $deLangVarList = array();

    public function init()
    {
        $this->deLangVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein quittungen");
        $this->deLangVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein quittungen");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein Belege");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherReceiptsHeader", "Gutschein Belege");
    }

    public function up()
    {
        foreach($this->deLangVarList as $langVar)
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
        foreach($this->deLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
