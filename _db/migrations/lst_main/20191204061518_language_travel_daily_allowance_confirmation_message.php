<?php

use Phinx\Migration\AbstractMigration;

class LanguageTravelDailyAllowanceConfirmationMessage extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", PRODUCT_GROUP__TRAVEL."-api-receipt_approve_by_employee_success-daily-allowance", "I confirm the number of daily allowance days.");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", PRODUCT_GROUP__TRAVEL."-api-receipt_approve_by_employee_success-daily-allowance", "Ich bestätige die angegebene Anzahl Tage je Verpflegungsmehraufwandkategorie.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", PRODUCT_GROUP__TRAVEL."-api-receipt_approve_by_employee_success-daily-allowance", "Ich bestätige die angegebene Anzahl Tage je Verpflegungsmehraufwandkategorie.");
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
