<?php


use Phinx\Migration\AbstractMigration;

class CreateGetStartedInConfig extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'get_started_document','',
							'x_app_license'::character varying,
							'file'::character varying,
							'2018-11-08 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='get_started_document'");
    }
}
