<?php

use Phinx\Migration\AbstractMigration;

class LanguageBonusVoucherValidation extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-yearly-limit-exceeded-with-month", "Bonus voucher service yearly limit will be exceeded in %month%");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-yearly-limit-exceeded-with-month", "Das jährliche Limit für den Prämien Gutschein wird in% Monat% überschritten.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-yearly-limit-exceeded-with-month", "Das jährliche Limit für den Prämien Gutschein wird in% Monat% überschritten.");
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
