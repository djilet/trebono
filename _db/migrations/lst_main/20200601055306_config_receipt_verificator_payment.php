<?php

use Phinx\Migration\AbstractMigration;

class ConfigReceiptVerificatorPayment extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $stmt = GetStatement();
        $langVarExists = $stmt->FetchList("SELECT * FROM language_variable WHERE tag_name='config-group-v_receipt_verification'");
        if(count($langVarExists) == 0)
        {
            $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-group-v_receipt_verification", "Akkordlohn Belegprüfung");
            $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-group-v_receipt_verification", "Commercial Parameters of trebono");
            $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-group-v_receipt_verification", "Akkordlohn Belegprüfung");
        }

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_verificator_payment_review", "Zahlung des Quittungsprüfers für den Status In Bearbeitung");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_verificator_payment_review", "Receipt verificator payment for status In review");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_verificator_payment_review", "Zahlung des Quittungsprüfers für den Status In Bearbeitung");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_verificator_payment_supervisor", "Zahlung des Quittungsprüfers für den Status für Vorgesetzten");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_verificator_payment_supervisor", "Receipt verificator payment for status For supervisor");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_verificator_payment_supervisor", "Zahlung des Quittungsprüfers für den Status für Vorgesetzten");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_verificator_payment_approve_proposed", "Zahlung des Quittungsprüfers für den Status Beleg bestätigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_verificator_payment_approve_proposed", "Receipt verificator payment for status Approve proposed");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_verificator_payment_approve_proposed", "Zahlung des Quittungsprüfers für den Status Beleg bestätigen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_verificator_payment_denied", "Zahlung des Quittungsprüfers für den Status Nicht verwendbar");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_verificator_payment_denied", "Receipt verificator payment for status Denied");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_verificator_payment_denied", "Zahlung des Quittungsprüfers für den Status Nicht verwendbar");
    }


    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }


        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, updated)
						VALUES 
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_verificator_payment_review',
							'0.1',
							'v_receipt_verification',
							'field-float',
							NOW()
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_verificator_payment_supervisor',
							'0.1',
							'v_receipt_verification',
							'field-float',
							NOW()
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_verificator_payment_approve_proposed',
							'0.1',
							'v_receipt_verification',
							'field-float',
							NOW()
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'receipt_verificator_payment_denied',
							'0.1',
							'v_receipt_verification',
							'field-float',
							NOW()
						)");
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }


        $this->execute("DELETE FROM config WHERE code IN (
            'receipt_verificator_payment_review',
            'receipt_verificator_payment_supervisor',
            'receipt_verificator_payment_approve_proposed',
            'receipt_verificator_payment_denied'
        )");
    }
}
