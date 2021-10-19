<?php

use Phinx\Migration\AbstractMigration;

class LanguageUserLastChangedReceiptStatus extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("de", "template", "receipt", "receipt_list.html", "UserLastChangedStatus", "Benutzer, der zuletzt den Status geändert hat"),
            new LangVar("en", "template", "receipt", "receipt_list.html", "UserLastChangedStatus", "User who last changed status"),
            new LangVar("tr", "template", "receipt", "receipt_list.html", "UserLastChangedStatus", "Benutzer, der zuletzt den Status geändert hat"),
        ];
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
