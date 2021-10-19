<?php

use Phinx\Migration\AbstractMigration;

class LanguageAdPaymentMonthEmptyReceipt extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "error-ad-receipt-yearly-payment-month-empty", "Der jährliche Anzeigenbeleg kann nicht genehmigt werden, wenn der Zahlungsmonat nicht ausgewählt ist");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "error-ad-receipt-yearly-payment-month-empty", "Yearly advertisement receipt can't be approved if payment month is not selected");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "error-ad-receipt-yearly-payment-month-empty", "Der jährliche Anzeigenbeleg kann nicht genehmigt werden, wenn der Zahlungsmonat nicht ausgewählt ist");
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
