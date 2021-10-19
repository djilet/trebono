<?php

use Phinx\Migration\AbstractMigration;

class LanguagePopupVariablesRemoveWrong extends AbstractMigration
{
    public function init()
    {
        // warning: contains tabs intentionally
        $this->langVarList = [
            new LangVar("de", "template", "core", "common", "confirm-disactivate	", "Möchten Sie \"% Title%\" wirklich deaktivieren?"),
            new LangVar("tr", "template", "core", "common", "confirm-disactivate	", "Möchten Sie \"% Title%\" wirklich deaktivieren?"),
        ];
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
