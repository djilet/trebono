<?php

use Phinx\Migration\AbstractMigration;

class ConfigVoucherDatevId extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-export_datev_voucher_client_id", "Datev voucher export client id");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-export_datev_voucher_client_id", "Datev Gutschein Rechnungs Export: unsere Mandaten ID");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-export_datev_voucher_client_id", "Datev Gutschein Rechnungs Export: unsere Mandaten ID");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'export_datev_voucher_client_id',
							'10017',
							'e_export'::character varying,
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
        $this->execute("DELETE FROM config WHERE code='export_datev_voucher_client_id'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
