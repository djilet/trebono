<?php

use Phinx\Migration\AbstractMigration;

class LanguageMigrationEmployeeDeactivation extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "company", "common", "deactivate-employee-error-employees-have-active-contracts", "Employees have active contracts"),
            new LangVar("en", "php", "company", "common", "deactivate-employee-error-employee-has-active-contracts", "Employee has active contracts"),

            new LangVar("de", "php", "company", "common", "deactivate-employee-error-employees-have-active-contracts", "Mitarbeiter haben aktive Verträge"),
            new LangVar("de", "php", "company", "common", "deactivate-employee-error-employee-has-active-contracts", "Mitarbeiter hat aktive Verträge"),

            new LangVar("tr", "php", "company", "common", "deactivate-employee-error-employees-have-active-contracts", "Mitarbeiter haben aktive Verträge"),
            new LangVar("tr", "php", "company", "common", "deactivate-employee-error-employee-has-active-contracts", "Mitarbeiter hat aktive Verträge"),
        ];
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
