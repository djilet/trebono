<?php

use Phinx\Migration\AbstractMigration;

class LanguageFilterVoucherId extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterVoucherID", "Gutschein ID");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterVoucherID", "Gutschein ID");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterVoucherID", "Voucher ID");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterVoucherIDPlaceholder", "ID hier eingeben");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterVoucherIDPlaceholder", "ID hier eingeben");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterVoucherIDPlaceholder", "Enter ID here");
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
