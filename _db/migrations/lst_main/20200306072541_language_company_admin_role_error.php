<?php

use Phinx\Migration\AbstractMigration;

class LanguageCompanyAdminRoleError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "start-date-company-admin-role-error", "To activate the Benefit Voucher Service you have to submit a signed SEPA direct debit. Please contact us at bestellung@trebono.de.");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "start-date-company-admin-role-error", "Bevor Sie den Sachbezug Gutschein Service aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "start-date-company-admin-role-error", "Bevor Sie den Sachbezug Gutschein Service aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");
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
