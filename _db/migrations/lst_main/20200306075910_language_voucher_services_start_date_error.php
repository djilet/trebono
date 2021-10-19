<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherServicesStartDateError extends AbstractMigration
{
    private $langVarList = array();
    private $delangVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("en", "php", "company", "common", "start-date-company-admin-role-error", "To activate the %service_title% you have to submit a signed SEPA direct debit. Please contact us at order@trebono.de.");
        $this->delangVarList[] = new LangVar("de", "php", "company", "common", "start-date-company-admin-role-error", "Bevor Sie den %service_title% aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");
        $this->delangVarList[] = new LangVar("tr", "php", "company", "common", "start-date-company-admin-role-error", "Bevor Sie den %service_title% aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-service-start-date-error", "To activate the %service_title% you have to submit a signed SEPA direct debit. Please contact us at order@trebono.de.");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-service-start-date-error", "Bevor Sie den %service_title% aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-service-start-date-error", "Bevor Sie den %service_title% aktivieren können müssen Sie eine SEPA Firmenlastschrift unterschrieben einreichen. Bitte setzten Sie sich mit uns unter bestellung@trebono.de in Verbindung.");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}