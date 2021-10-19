<?php


use Phinx\Migration\AbstractMigration;

class SeparateDeniedReceiptPushConfig extends AbstractMigration
{
    public function up()
    {
    	$text = Config::GetConfigValue("push_receipt_processed");
    	
		$this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES ( 
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_receipt_denied',".Connection::GetSQLString($text).",
							'p_push'::character varying,
							'plain'::character varying,
							'2018-10-16 00:00:00'
						)");
    }
    
    public function down()
    {
    	$this->execute("DELETE FROM config WHERE code='push_receipt_denied'");
    }
}
