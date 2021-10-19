<?php

use Phinx\Migration\AbstractMigration;

class LanguageMiscEmployeeFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "employee-filter-restriction-misc", "There are too many employees to process. Filter was run through first %employee_filter_count% employees");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "employee-filter-restriction-misc", "Es sind zu viele Mitarbeiter für die Bearbeitung vorhanden. Der Filter wurde bei den ersten %employee_filter_count% Mitarbeitern durchgeführt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "employee-filter-restriction-misc", "Es sind zu viele Mitarbeiter für die Bearbeitung vorhanden. Der Filter wurde bei den ersten %employee_filter_count% Mitarbeitern durchgeführt");
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
