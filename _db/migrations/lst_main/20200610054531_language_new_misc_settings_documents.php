<?php

use Phinx\Migration\AbstractMigration;

class LanguageNewMiscSettingsDocuments extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_5", "Business terms document 5");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_5", "Nicht belegt 5");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_5", "Nicht belegt 5");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-business_terms_6", "Business terms document 6");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-business_terms_6", "Nicht belegt 6");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-business_terms_6", "Nicht belegt 6");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated, sort_order)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_5',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							9
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'business_terms_6',
							'',
							'x_app_license'::character varying,
							'file'::character varying,
							NOW(),
							10
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='business_terms_5' OR code='business_terms_6'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

}
