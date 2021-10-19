<?php

use Phinx\Migration\AbstractMigration;

class GiftsVoucherStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-statistics", "Gift Voucher Service statistics");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-statistics", "Geschenk Gutschein Statistik");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-statistics", "Geschenk Gutschein Statistik");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-yearly-statistics", "Gift Voucher Service yearly statistics");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-yearly-statistics", "Geschenk Gutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__GIFT_VOUCHER."-yearly-statistics", "Geschenk Gutschein jährliche Statistik");
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
