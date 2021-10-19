<?php

use Phinx\Migration\AbstractMigration;

class LanguageDeactivationReasonError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "deactivation-reason-is-empty", "Deactivation reason cannot be empty");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "deactivation-reason-is-empty", "Der Deaktivierungsgrund kann nicht leer sein");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "deactivation-reason-is-empty", "Der Deaktivierungsgrund kann nicht leer sein");
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
