<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherDateInvalid extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-date-invalid", "Gutscheine können nicht für vergangene Daten erstellt werden.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-date-invalid", "Gutscheine können nicht für vergangene Daten erstellt werden.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-date-invalid", "Vouchers can't be created for past dates.");

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
