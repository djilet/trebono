<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherEmployeerGrant extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. Monthly Value");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. monatl. Wert");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. monatl. Wert");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__employer_grant", "Max. Monthly Value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__employer_grant", "Max. monatl. Wert");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__employer_grant", "Max. monatl. Wert");
    }

    public function up()
    {
        $this->execute("UPDATE option SET code='benefit_voucher__main__employer_grant' WHERE code='benefit_voucher__main__max_monthly'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("UPDATE option SET code='benefit_voucher__main__max_monthly' WHERE code='benefit_voucher__main__employer_grant'");

        foreach($this->delLangVarList as $langVar)
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
