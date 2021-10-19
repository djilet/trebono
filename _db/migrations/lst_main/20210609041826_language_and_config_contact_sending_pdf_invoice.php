<?php

use Phinx\Migration\AbstractMigration;

class LanguageAndConfigContactSendingPdfInvoice extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "template", "company", "contact_edit.html", "SendingPdfInvoice", "Send invoice as PDF to invoice contact"),
            new LangVar("de", "template", "company", "contact_edit.html", "SendingPdfInvoice", "Rechnung als PDF an Rechnungskontakt senden"),
            new LangVar("tr", "template", "company", "contact_edit.html", "SendingPdfInvoice", "Rechnung als PDF an Rechnungskontakt senden"),

            new LangVar("en", "template", "company", "contact_edit.html", "Agree", "Agree"),
            new LangVar("de", "template", "company", "contact_edit.html", "Agree", "Vereinbaren"),
            new LangVar("tr", "template", "company", "contact_edit.html", "Agree", "Vereinbaren"),

            new LangVar("en", "template", "company", "contact_edit.html", "Deny", "Deny"),
            new LangVar("de", "template", "company", "contact_edit.html", "Deny", "Leugnen"),
            new LangVar("tr", "template", "company", "contact_edit.html", "Deny", "Leugnen"),

            new LangVar("en", "php", "core", "common", "config-agreement_of_sending_pdf_invoice", "Agreement of sending PDF invoice"),
            new LangVar("de", "php", "core", "common", "config-agreement_of_sending_pdf_invoice", "Vereinbarung über den Versand der PDF-Rechnung"),
            new LangVar("tr", "php", "core", "common", "config-agreement_of_sending_pdf_invoice", "Vereinbarung über den Versand der PDF-Rechnung"),
        ];
    }

    public function up()
    {
        $this->execute("INSERT INTO config (config_id, code, value, group_code, editor, sort_order)
						VALUES (
							nextval('\"config_ConfigID_seq\"'::regclass),
							'agreement_of_sending_pdf_invoice',
							'Stimmen Sie zu, die Rechnung im PDF-Format zu versenden?',
							'misc'::character varying,
							'plain'::character varying,
							'8'
						)");

        $config = $this->fetchRow("SELECT config_id FROM config WHERE code='agreement_of_sending_pdf_invoice'");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("INSERT INTO config_history (user_id, config_id, value, date_from, created)
						VALUES (
							".Connection::GetSQLString(SERVICE_USER_ID).",
							".$config["config_id"].",
							'Stimmen Sie zu, die Rechnung im PDF-Format zu versenden?',
							".Connection::GetSQLDateTime(GetCurrentDateTime()).",
							".Connection::GetSQLDateTime(GetCurrentDateTime())."
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $config = $this->fetchRow("SELECT config_id FROM config WHERE code='agreement_of_sending_pdf_invoice'");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM config_history WHERE config_id=".$config["config_id"]);

        $this->execute("DELETE FROM config WHERE code='agreement_of_sending_pdf_invoice'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

    }
}
