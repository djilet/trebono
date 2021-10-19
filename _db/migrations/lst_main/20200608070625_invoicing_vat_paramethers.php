<?php

use Phinx\Migration\AbstractMigration;

class InvoicingVatParamethers extends AbstractMigration
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

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-invoice_vat", "MwSt.% Für Servicerechnung");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-invoice_vat", "VAT% for Service Invoice");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-invoice_vat", "MwSt.% Für Servicerechnung");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-voucher_invoice_vat", "MwSt.% Für Gutscheinrechnung");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-voucher_invoice_vat", "VAT% for Voucher Invoice");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-voucher_invoice_vat", "MwSt.% Für Gutscheinrechnung");

        $this->langVarList[] = new LangVar("de", "template", "core", "config_edit.html", "DateOfParams", "Datum der Parameter");
        $this->langVarList[] = new LangVar("en", "template", "core", "config_edit.html", "DateOfParams", "Date of params");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_edit.html", "DateOfParams", "Datum der Parameter");

        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "DateFrom", "Gültig ab Datum");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "DateFrom", "Valid from date");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "DateFrom", "Gültig ab Datum");

        $this->langVarList[] = new LangVar("de", "template", "core", "block_config_history.html", "Value", "Werte");
        $this->langVarList[] = new LangVar("en", "template", "core", "block_config_history.html", "Value", "Value");
        $this->langVarList[] = new LangVar("tr", "template", "core", "block_config_history.html", "Value", "Werte");
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
							'invoice_vat',
							'19',
							'v_receipt_verification',
							'field-float',
							NOW()
						),
						(
							nextval('\"config_ConfigID_seq\"'::regclass),
							'voucher_invoice_vat',
							'0',
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
            'invoice_vat',
            'voucher_invoice_vat'
        )");
    }
}
