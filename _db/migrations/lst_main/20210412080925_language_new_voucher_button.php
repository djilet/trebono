<?php

use Phinx\Migration\AbstractMigration;

class LanguageNewVoucherButton extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "AddNewVoucher", "Neuen Gutschein hinzufügen");
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "AddNewVoucher", "Add new voucher");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "AddNewVoucher", "Neuen Gutschein hinzufügen");
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
