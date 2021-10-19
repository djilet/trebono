<?php

use Phinx\Migration\AbstractMigration;

class LanguagePopupVariables extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "common", "confirm-disactivate	", "Möchten Sie \"% Title%\" wirklich deaktivieren?");
        $this->langVarList[] = new LangVar("tr", "template", "core", "common", "confirm-disactivate	", "Möchten Sie \"% Title%\" wirklich deaktivieren?");
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
