<?php

use Phinx\Migration\AbstractMigration;

class LanguageDashboardCollapse extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        /*$this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "Expand", "Details");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "Collapse", "Collapse");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "Collapse", "Einklappen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "Collapse", "Einklappen");*/
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
