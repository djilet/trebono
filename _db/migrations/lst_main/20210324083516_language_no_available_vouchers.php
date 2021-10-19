<?php

use Phinx\Migration\AbstractMigration;

class LanguageNoAvailableVouchers extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "no-available-vouchers-description", "Nachricht, in der erklärt wird, dass keine Gutscheine verfügbar sind (BVS und BoVS)");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "no-available-vouchers-description", "Message explaining that there are no available vouchers (BVS and BoVS)");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "no-available-vouchers-description", "Nachricht, in der erklärt wird, dass keine Gutscheine verfügbar sind (BVS und BoVS)");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", "no-available-vouchers", "Tut uns Leid, zur Zeit haben Sie keine verfügbaren Gutscheine mehr. Bitte warten Sie, bis Sie einen neuen Gutschein erhalten. Vielen Dank.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "no-available-vouchers", "Sorry, you have used all your vouchers. Please wait until you receive a new voucher. Many thanks.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "no-available-vouchers", "Tut uns Leid, zur Zeit haben Sie keine verfügbaren Gutscheine mehr. Bitte warten Sie, bis Sie einen neuen Gutschein erhalten. Vielen Dank.");
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
