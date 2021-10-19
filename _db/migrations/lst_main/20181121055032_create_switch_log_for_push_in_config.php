<?php


use Phinx\Migration\AbstractMigration;

class CreateSwitchLogForPushInConfig extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'log_push_notification','Y',
							'p_push'::character varying,
							'flag'::character varying,
							'2018-11-21 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='log_push_notification'");
    }
}
