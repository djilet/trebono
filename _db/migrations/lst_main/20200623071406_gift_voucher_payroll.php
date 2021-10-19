<?php

use Phinx\Migration\AbstractMigration;

class GiftVoucherPayroll extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-group-gift_voucher", "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-group-gift_voucher", "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-group-gift_voucher", "Geschenk Gutschein");
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
