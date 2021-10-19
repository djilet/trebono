<?php

use Phinx\Migration\AbstractMigration;

class LanguageMenuRearrange extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-main-dashboard", "Dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-main-dashboard", "Dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-main-dashboard", "Dashboard");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-main-logging", "Protokoll");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-main-logging", "Logs");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-main-logging", "Protokoll");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-main-settings", "Einstellungen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-main-settings", "Settings");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-main-settings", "Einstellungen");
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
