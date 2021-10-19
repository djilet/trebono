<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherServicesFullTitle extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-full-title-benefit_voucher__main", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-full-title-benefit_voucher__main", "Sachbezug Gutschein Service");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-full-title-benefit_voucher__main", "Sachbezug Gutschein Service");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-full-title-food_voucher__main", "Food Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-full-title-food_voucher__main", "Essensmarken Gutschein Service");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-full-title-food_voucher__main", "Essensmarken Gutschein Service");
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
