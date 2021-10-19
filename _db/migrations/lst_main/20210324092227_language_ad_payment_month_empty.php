<?php

use Phinx\Migration\AbstractMigration;

class LanguageAdPaymentMonthEmpty extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "error-ad-yearly-payment-month-empty", "Wenn die Option für den jährlichen Beleg ausgewählt ist, darf der Zahlungsmonat nicht leer sein");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "error-ad-yearly-payment-month-empty", "If yearly receipt option is chosen, payment month can't be empty");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "error-ad-yearly-payment-month-empty", "Wenn die Option für den jährlichen Beleg ausgewählt ist, darf der Zahlungsmonat nicht leer sein");
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
