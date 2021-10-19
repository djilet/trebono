<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherBillingLanguageFix extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__main", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__main", "Sachbezug Gutschein");

        $this->delLangVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__advanced_security", "Benefit Voucher Service Advanced Security");
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__advanced_security", "Sachbezug Gutschein Erweiterte Sicherheit");
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
