<?php

use Phinx\Migration\AbstractMigration;

class LanguageErrorsVoucherDate extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-date-not-1st", "Gutscheine können nur am ersten Tag der folgenden Monate erstellt werden.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-date-not-1st", "Gutscheine können nur am ersten Tag der folgenden Monate erstellt werden.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-date-not-1st", "Vouchers can only be created on a 1st of the following months.");

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
