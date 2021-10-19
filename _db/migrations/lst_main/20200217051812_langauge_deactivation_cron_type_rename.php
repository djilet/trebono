<?php

use Phinx\Migration\AbstractMigration;

class LangaugeDeactivationCronTypeRename extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-deactivate", "Deactivation of units");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-deactivate", "Deaktivierung von Einheiten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-deactivate", "Deaktivierung von Einheiten");

        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deactivation of employee");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deaktivierung des Mitarbeiters");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deaktivierung des Mitarbeiters");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
