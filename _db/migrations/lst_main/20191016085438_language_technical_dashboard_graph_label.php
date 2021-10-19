<?php

use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardGraphLabel extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "User", "User");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "User", "Benutzer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "User", "Benutzer");
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
