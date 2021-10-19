<?php


use Phinx\Migration\AbstractMigration;

class ReasonListForVouchers extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'voucher_reason','Year bonus',
							'misc'::character varying,
							'plain'::character varying,
							'2018-12-17 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='voucher_reason'");
    }
}
