<?php


use Phinx\Migration\AbstractMigration;

class MobileConfirmationStringsMiscSettings extends AbstractMigration
{
	public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'mobile_app_receipt_confirm_description',
							'Bitte bestätigen Sie, daß 
- die obige Anzahl Mahlzeiten in dem Beleg enthalten sind
- das Bild mit dem Originalbeleg übereinstimmt
- der Beleg gemäß Richtlinie aufgenommen wurde.
Sollte eine andere Anzahl Mahlzeiten im Beleg enthalten sein, 
bestätigen Sie bitte nicht, sondern schreiben uns eine Nachricht im Chat.',
							'm_mobile_app'::character varying,
							'plain'::character varying,
							'2018-11-08 00:00:00'
						)");
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'mobile_app_receipt_confirm_button',
							'Bestätigen Sie, dass die Informationen korrekt sind',
							'm_mobile_app'::character varying,
							'plain'::character varying,
							'2018-11-08 00:00:00'
						)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='mobile_app_receipt_confirm_description'");
        $this->execute("DELETE FROM config WHERE code='mobile_app_receipt_confirm_button'");
    }
}
