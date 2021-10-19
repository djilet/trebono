<?php

use Phinx\Migration\AbstractMigration;

class ReceiptTravelDaysAmount extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "DaysAmountOver16", "Amount of days >= 16 hours");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "DaysAmountOver16", ">= 16 Stunden");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "DaysAmountOver16", ">= 16 Stunden");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "DaysAmountUnder16", "Amount of days > 8 < 16 hours");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "DaysAmountUnder16", "> 8 < 16 Stunden");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "DaysAmountUnder16", "> 8 < 16 Stunden");
    }

    public function up()
    {

        $this->table("receipt")
            ->addColumn("days_amount_under_16", "integer", ["null" => true])
            ->addColumn("days_amount_over_16", "integer", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("receipt")
            ->removeColumn("days_amount_under_16")
            ->removeColumn("days_amount_over_16")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
