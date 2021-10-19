<?php

use Phinx\Migration\AbstractMigration;

class LanguageInterruptionErrors extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "contract-intersection-with-interruption-found", "Contract intersections with interruption service are not allowed");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "contract-intersection-with-interruption-found", "Vertragskreuzungen mit Unterbrechungsdienst sind nicht zulässig");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "contract-intersection-with-interruption-found", "Vertragskreuzungen mit Unterbrechungsdienst sind nicht zulässig");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-interruption-contract", "Voucher cannot be added with active interruption contract");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-interruption-contract", "Gutschein kann nicht mit aktivem Unterbrechungsvertrag hinzugefügt werden");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-interruption-contract", "Gutschein kann nicht mit aktivem Unterbrechungsvertrag hinzugefügt werden");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_employee_deactivated", "Employee was deactivated");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_employee_deactivated", "Mitarbeiter wurde deaktiviert");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_employee_deactivated", "Mitarbeiter wurde deaktiviert");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_employee_deactivated_fired", "Employee was deactivated with reason end of contract");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_employee_deactivated_fired", "Der Mitarbeiter wurde mit dem Grund Vertragsende deaktiviert");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_employee_deactivated_fired", "Der Mitarbeiter wurde mit dem Grund Vertragsende deaktiviert");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_no_active_contract", "No active contract");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_no_active_contract", "Kein aktiver Vertrag");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_no_active_contract", "Kein aktiver Vertrag");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_no_active_contract_no_vouchers", "No active contract and no vouchers");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_no_active_contract_no_vouchers", "Kein aktiver Vertrag und keine Gutscheine");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_no_active_contract_no_vouchers", "Kein aktiver Vertrag und keine Gutscheine");
    }

    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_employee_deactivated', 'mitarbeiter wurde deaktiviert','r_autodeny', 'plain', ".$updated.")");

        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_employee_deactivated_fired', 'der Mitarbeiter wurde mit dem Grund Vertragsende deaktiviert','r_autodeny', 'plain', ".$updated.")");

        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_no_active_contract', 'kein aktiver Vertrag','r_autodeny', 'plain', ".$updated.")");

        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_no_active_contract_no_vouchers', 'kein aktiver Vertrag und keine Gutscheine','r_autodeny', 'plain', ".$updated.")");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_employee_deactivated'");
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_employee_deactivated_fired'");
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_no_active_contract'");
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_no_active_contract_no_vouchers'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
