<?php

use Phinx\Migration\AbstractMigration;

class TechnicalDashboardSplit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "technical-dashboard-section-receipt", "Receipts");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "technical-dashboard-section-receipt", "Beleg");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "technical-dashboard-section-receipt", "Beleg");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "technical-dashboard-section-storage", "Storage");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "technical-dashboard-section-storage", "Lager");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "technical-dashboard-section-storage", "Lager");
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
