<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptFilterHasChat extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterHasChat", "Has chat");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterHasChat", "Hat Chat");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterHasChat", "Hat Chat");
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
