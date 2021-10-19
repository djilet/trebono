<?php

use Phinx\Migration\AbstractMigration;

class LanguageFoodCalendarUnits extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_calendar.html", "FoodUnit", "Food service unit");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_calendar.html", "FoodUnit", "Essen service einheit");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_calendar.html", "FoodUnit", "Essen service einheit");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_calendar.html", "FoodVoucherUnit", "Food voucher service unit");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_calendar.html", "FoodVoucherUnit", "Essen gutschein service einheit");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_calendar.html", "FoodVoucherUnit", "Essen gutschein service einheit");
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
