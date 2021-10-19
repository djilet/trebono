<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherLockOut extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-benefit-voucher-not-found", "For the chosen receipt specific category no vouchers are available anymore");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-benefit-voucher-not-found", "Für die ausgewählte belegspezifische Kategorie sind keine Gutscheine mehr verfügbar");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-benefit-voucher-not-found", "Für die ausgewählte belegspezifische Kategorie sind keine Gutscheine mehr verfügbar");
    }

    public function up()
    {
        $this->table("voucher")->addColumn("receipt_ids", "string", ["length" => 255, "null" => true])->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("voucher")->removeColumn("receipt_ids");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
