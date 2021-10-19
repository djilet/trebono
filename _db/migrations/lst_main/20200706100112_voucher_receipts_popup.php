<?php

use Phinx\Migration\AbstractMigration;

class VoucherReceiptsPopup extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "VoucherID", "Gutschein ID");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "VoucherID", "Voucher ID");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "VoucherID", "Gutschein ID");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "Created", "Created");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "Created", "Erstellt am");
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
