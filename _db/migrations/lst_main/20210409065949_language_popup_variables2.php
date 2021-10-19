<?php

use Phinx\Migration\AbstractMigration;

class LanguagePopupVariables2 extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("de", "php", "core", "common", "confirm-disactivate", "Möchten Sie \"%Title%\" wirklich deaktivieren?"),
            new LangVar("tr", "php", "core", "common", "confirm-disactivate", "Möchten Sie \"%Title%\" wirklich deaktivieren?"),
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
