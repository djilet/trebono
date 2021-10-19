<?php

use Phinx\Migration\AbstractMigration;

class LanguageMustRelogin extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "session-expired-must-relogin", "Your session has expired. Please, login again to continue working"),
            new LangVar("de", "php", "core", "common", "session-expired-must-relogin", "Deine Sitzung ist abgelaufen. Bitte melden Sie sich erneut an, um weiterarbeiten zu können"),
            new LangVar("tr", "php", "core", "common", "session-expired-must-relogin", "Deine Sitzung ist abgelaufen. Bitte melden Sie sich erneut an, um weiterarbeiten zu können"),
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
