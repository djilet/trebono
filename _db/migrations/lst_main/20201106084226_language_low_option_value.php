<?php

use Phinx\Migration\AbstractMigration;

class LanguageLowOptionValue extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-lower-then-approved-value", "Limit value is lower than amount approved by employee %employee_list%");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-lower-then-approved-value", "Der Grenzwert ist niedriger als der vom Mitarbeiter genehmigte Betrag %employee_list%");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-lower-then-approved-value", "Der Grenzwert ist niedriger als der vom Mitarbeiter genehmigte Betrag %employee_list%");
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
