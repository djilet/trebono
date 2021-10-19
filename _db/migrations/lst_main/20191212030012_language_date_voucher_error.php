<?php

use Phinx\Migration\AbstractMigration;

class LanguageDateVoucherError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "voucher-date-not-1st", "Gutscheine können nur am ersten Tag der folgenden Monate erstellt werden.");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "voucher-date-not-1st", "Gutscheine können nur am ersten Tag der folgenden Monate erstellt werden.");
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "voucher-date-not-1st", "Vouchers can only be created on a 1st of the following months.");

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
