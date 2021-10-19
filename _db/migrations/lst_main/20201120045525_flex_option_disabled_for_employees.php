<?php

use Phinx\Migration\AbstractMigration;

class FlexOptionDisabledForEmployees extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-flex-disabled-for-employees", "You enabled flex option on company level, but following employees have it disabled: %employee_list%");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-flex-disabled-for-employees", "Sie haben die Flex-Option auf Unternehmensebene aktiviert, aber bei folgenden Mitarbeitern ist sie deaktiviert: %employee_list%");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-flex-disabled-for-employees", "Sie haben die Flex-Option auf Unternehmensebene aktiviert, aber bei folgenden Mitarbeitern ist sie deaktiviert: %employee_list%");
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
