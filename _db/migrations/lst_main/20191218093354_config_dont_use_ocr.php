<?php

use Phinx\Migration\AbstractMigration;

class ConfigDontUseOcr extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-do_not_use_ocr", "Verwenden Sie keine OCR");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-do_not_use_ocr", "Don't use OCR");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-do_not_use_ocr", "Verwenden Sie keine OCR");
    }


    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }


        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'do_not_use_ocr','N',
							'misc'::character varying,
							'flag'::character varying,
							NOW()
						)");
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }


        $this->execute("DELETE FROM config WHERE code='do_not_use_ocr'");
    }
}
