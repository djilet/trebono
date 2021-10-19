<?php


use Phinx\Migration\AbstractMigration;

class CreatePushRemindTextInConfig extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_remind_text','Sie haben noch %unused_month% Euro für diesen Monat für Ihren %service% zur Verfügung. Sie haben %days_left% Tage um alle Belege einzureichen.',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-11-02 00:00:00'
						)");
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_remind_text_food','Sie haben noch %unused_month% digitale Essensmarken zur Verfügung. Sie haben %days_left% Tage um alle Belege einzureichen.',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-11-02 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='push_remind_text'");
        $this->execute("DELETE FROM config WHERE code='push_remind_text_food'");
    }
}
