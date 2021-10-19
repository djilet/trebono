<?php

use Phinx\Migration\AbstractMigration;

class CreateDenialReasonsInConfig extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_denial_comment','Dear %first_name% %last_name% your receipt %legal_receipt_id% was denied by the following reason(s): %denial_reason%.',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-11-02 00:00:00'
						)");
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_denial_reason','unable to apply',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-11-02 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_denial_comment'");
        $this->execute("DELETE FROM config WHERE code='receipt_denial_reason'");
    }
}
