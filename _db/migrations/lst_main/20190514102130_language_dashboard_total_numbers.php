<?php


use Phinx\Migration\AbstractMigration;

class LanguageDashboardTotalNumbers extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "TotalCountSeparator", " / ");
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "TotalCountSeparator", " / ");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "TotalCountSeparator", " / ");
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
