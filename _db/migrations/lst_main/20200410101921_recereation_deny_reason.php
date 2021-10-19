<?php

use Phinx\Migration\AbstractMigration;

class RecereationDenyReason extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_by_employee", "Receipt denied by employee");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_by_employee", "Quittung vom Mitarbeiter abgelehnt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_by_employee", "Quittung vom Mitarbeiter abgelehnt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_recreation", "Text for automatic denials (Recreation service)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_recreation", "Text für die automatische Ablehnung (Erholungsbeleg verweigert)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_recreation", "Text für die automatische Ablehnung (Erholungsbeleg verweigert)");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_autodeny_by_employee',
							'Receipt was denied by employee',
							'r_autodeny'::character varying,
							'plain'::character varying,
							NOW()
						)");

        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_autodeny_recreation',
							'Recreation receipt was autodenied by system',
							'r_autodeny'::character varying,
							'plain'::character varying,
							NOW()
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_by_employee' OR code='receipt_autodeny_recreation'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
