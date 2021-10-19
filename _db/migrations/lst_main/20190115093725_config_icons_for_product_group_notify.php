<?php


use Phinx\Migration\AbstractMigration;

class ConfigIconsForProductGroupNotify extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'icon_actions','',
							'm_mobile_app_icons'::character varying,
							'file'::character varying,
							'2019-01-15 00:00:00'
						)");
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'icon_messages','',
							'm_mobile_app_icons'::character varying,
							'file'::character varying,
							'2019-01-15 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='icon_actions'");
        $this->execute("DELETE FROM config WHERE code='icon_messages'");
    }
}
