<?php

use Phinx\Migration\AbstractMigration;

class CheckOcrLaterDate2 extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_not_a_receipt", "Text for the automatic denials (receipt file is not receipt after OCR failure)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_not_a_receipt", "Text für die automatische Ablehnung (Quittungsdatei wird nach OCR-Fehler nicht empfangen)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_not_a_receipt", "Text für die automatische Ablehnung (Quittungsdatei wird nach OCR-Fehler nicht empfangen)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-message_receipt_autodeny_not_a_receipt", "Text of comment which send with denial reason after OCR failure");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-message_receipt_autodeny_not_a_receipt", "Kommentartext, der nach einem OCR-Fehler mit Verweigerungsgrund gesendet wird");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-message_receipt_autodeny_not_a_receipt", "Kommentartext, der nach einem OCR-Fehler mit Verweigerungsgrund gesendet wird");
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_autodeny_not_a_receipt',
							'Receipt file doesn''t contain receipt',
							'r_autodeny'::character varying,
							'plain'::character varying,
							NOW()
						)");

        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'message_receipt_autodeny_not_a_receipt',
							'Hallo %salutation% %first_name% %last_name%. Wir haben Ihr Foto nach technischen Problemen überprüft und unser System hat darauf keinen Beleg gefunden. Vielen Dank, Ihr trebono Team.',
							'p_push'::character varying,
							'plain'::character varying,
							NOW()
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_not_a_receipt' OR code='message_receipt_autodeny_not_a_receipt'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
