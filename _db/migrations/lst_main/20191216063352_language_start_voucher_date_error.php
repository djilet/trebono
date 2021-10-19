<?php

use Phinx\Migration\AbstractMigration;

class LanguageStartVoucherDateError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-start-date-not-1st", "Der BVS-Dienst kann erst zum 1. eines zukünftigen Monats beginnen.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-start-date-not-1st", "Der BVS-Dienst kann erst zum 1. eines zukünftigen Monats beginnen.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-start-date-not-1st", "BVS service can start only at a 1st of a future month.");

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
