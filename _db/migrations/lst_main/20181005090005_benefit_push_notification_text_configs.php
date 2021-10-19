<?php


use Phinx\Migration\AbstractMigration;

class BenefitPushNotificationTextConfigs extends AbstractMigration
{
    public function up()
    {
    	$this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES ( 
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_receipt_benefit_1','Ihre eingereichte Stromrechnung endet diesen Monat. Bitte reichen Sie schnellst möglich Ihre neue Stromrechnung ein. Vielen Dank!',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-10-05 00:00:00'
						)");
    	
    	$this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES ( 
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_receipt_benefit_2_3','Bitte denken Sie an das Einreichen Ihrer neuen Stromrechnung.',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-10-05 00:00:00'
						)");
    	
    	$this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES ( 
							nextval('\"config_ConfigID_seq\"'::regclass),
							'push_receipt_benefit_4','Leider haben Sie noch keine neue Stromrechnung eingereicht. Dies ist der letzte Monat, indem Sie Ihren Sachbezug in Höhe von %max_monthly% € bekommen. Wenn Sie diesen Sachbezug weiter bekommen wollen, dann reichen Sie Ihre Stromrechnung bis zum Ende diesen Monats ein. Vielen Dank!',
							'p_push'::character varying,
							'plain'::character varying,
							'2018-10-05 00:00:00'
						)");
    }
    
    public function down()
    {
    	$this->execute("DELETE FROM config WHERE code IN ('push_receipt_benefit_1', 'push_receipt_benefit_2_3', 'push_receipt_benefit_4')");
    }
}
