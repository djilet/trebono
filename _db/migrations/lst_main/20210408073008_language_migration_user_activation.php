<?php

use Phinx\Migration\AbstractMigration;

class LanguageMigrationUserActivation extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("de", "php", "core", "common", "user-is-activated", "Benutzer %UserList% ist jetzt aktiv"),
            new LangVar("de", "php", "core", "common", "user-is-disactivated", "Benutzer %UserList% ist jetzt inaktiv"),

            new LangVar("tr", "php", "core", "common", "user-is-activated", "Benutzer %UserList% ist jetzt aktiv"),
            new LangVar("tr", "php", "core", "common", "user-is-disactivated", "Benutzer %UserList% ist jetzt inaktiv"),
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
