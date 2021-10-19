<?php

use Phinx\Migration\AbstractMigration;

class LanguageServiceIsActive extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "service-is-active", 'Warning: Service "%service%" is active'),
            new LangVar("de", "php", "core", "common", "service-is-active", 'Warnung: Der Dienst "%service%" ist aktiv'),
            new LangVar("tr", "php", "core", "common", "service-is-active", 'Warnung: Der Dienst "%service%" ist aktiv'),
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
