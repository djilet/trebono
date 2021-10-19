<?php

use Phinx\Migration\AbstractMigration;

class LangiageGiftVoucherValidation extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-yearly-qty-limit-exceeded-month", "Gift voucher service yearly count limit will be exceeded in %month%");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-yearly-qty-limit-exceeded-month", "Das jährliche Zähllimit für den Geschenkgutscheinservice wird in %month% überschritten");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-yearly-qty-limit-exceeded-month", "Das jährliche Zähllimit für den Geschenkgutscheinservice wird in %month% überschritten");
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
