<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptVoucherAmount extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_receipts.html", "ApprovedAmount", "Approved amount");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_receipts.html", "ApprovedAmount", "Genehmigter Betrag");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_receipts.html", "ApprovedAmount", "Genehmigter Betrag");
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
