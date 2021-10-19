<?php


use Phinx\Migration\AbstractMigration;

class BonusPushText extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_remind_text_bonus','Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende Prämiengutscheine offen: %voucher_list%. Vielen Dank, Ihr FINEasy Team.',
							'p_push'::character varying,
							'plain'::character varying,
							'2019-01-09 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='push_remind_text_bonus'");
    }
}
