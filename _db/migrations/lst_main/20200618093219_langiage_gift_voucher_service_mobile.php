<?php

use Phinx\Migration\AbstractMigration;

class LangiageGiftVoucherServiceMobile extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-confirmation_description", "The document was recorded according to the organizational instructions. If another amount should be included in the receipt, please do not confirm, but send us a message here in voucher chat.");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-receipt_approve_by_employee_success", "Please destroy your receipt now");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", PRODUCT_GROUP__GIFT_VOUCHER."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
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
