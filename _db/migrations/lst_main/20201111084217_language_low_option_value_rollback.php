<?php

use Phinx\Migration\AbstractMigration;

class LanguageLowOptionValueRollback extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-value-next-month", "Since this option is crucial for calculation, new value will take affect in the beginning on next month");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-value-next-month", "Da diese Option für die Berechnung von entscheidender Bedeutung ist, wird der neue Wert Anfang nächsten Monats wirksam");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-value-next-month", "Da diese Option für die Berechnung von entscheidender Bedeutung ist, wird der neue Wert Anfang nächsten Monats wirksam");

        $this->delLangVarList[] = new LangVar("en", "php", "product", "common", "option-lower-then-approved-value", "Limit value is lower than amount approved by employee %employee_list%");
        $this->delLangVarList[] = new LangVar("de", "php", "product", "common", "option-lower-then-approved-value", "Der Grenzwert ist niedriger als der vom Mitarbeiter genehmigte Betrag %employee_list%");
        $this->delLangVarList[] = new LangVar("tr", "php", "product", "common", "option-lower-then-approved-value", "Der Grenzwert ist niedriger als der vom Mitarbeiter genehmigte Betrag %employee_list%");
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
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
