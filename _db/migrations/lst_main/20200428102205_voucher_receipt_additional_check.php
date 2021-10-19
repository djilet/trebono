<?php

use Phinx\Migration\AbstractMigration;

class VoucherReceiptAdditionalCheck extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {

        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "approved-receipt-has-no-mapping", "Please, stop verifying %product_translation%. There is something wrong with voucher assignment. Please make a screenshot and note the receipt number and send it immediately to development!");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "approved-receipt-has-no-mapping", "Bitte stopp sofort die Belegkontrolle für %product_translation%. Es liegt ein Problem mit dem Gutschein vor. Bitte notiere die Belegnummer mache einen Screenshot und schicke alles an die Entwicklung!");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "approved-receipt-has-no-mapping", "Bitte stopp sofort die Belegkontrolle für %product_translation%. Es liegt ein Problem mit dem Gutschein vor. Bitte notiere die Belegnummer mache einen Screenshot und schicke alles an die Entwicklung!");
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
