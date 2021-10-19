<?php

use Phinx\Migration\AbstractMigration;

class GlobalSettingsAsLimit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-exceeds-global-value", "Value exceeds limit from global settings");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-exceeds-global-value", "Der Wert überschreitet den Grenzwert aus globalen Einstellungen");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-exceeds-global-value", "Der Wert überschreitet den Grenzwert aus globalen Einstellungen");
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
