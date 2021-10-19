<?php


use Phinx\Migration\AbstractMigration;

class LanguageReplacemetsFix2 extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-payment_month_qty", "Zahlungen Monat");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-payment_month_qty", "Number of payment month");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-payment_month_qty", "Zahlungen Monat");
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
