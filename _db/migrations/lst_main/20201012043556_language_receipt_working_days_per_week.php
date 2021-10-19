<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptWorkingDaysPerWeek extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "employee-working-days-per-week", "Working days per week");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "employee-working-days-per-week", "Arbeitstage pro Woche");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "employee-working-days-per-week", "Arbeitstage pro Woche");
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
