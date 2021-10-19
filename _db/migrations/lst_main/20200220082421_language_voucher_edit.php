<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherEdit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "Count", "Count");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "Count", "Anzahl");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "Count", "Anzahl");
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
