<?php

use Phinx\Migration\AbstractMigration;

class LanguagePushFilterNoResultsFound extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "PushFilterNoResults", "No suitable results were found");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "PushFilterNoResults", "Es wurden keine passenden Ergebnisse gefunden");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "PushFilterNoResults", "Es wurden keine passenden Ergebnisse gefunden");
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
