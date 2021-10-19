<?php

use Phinx\Migration\AbstractMigration;

class RecreationChangesPdf extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_list.html", "RecreationConfirmations", "Recreation confirmations PDF");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_list.html", "RecreationConfirmations", "Freizeitbestätigungen PDF");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_list.html", "RecreationConfirmations", "Freizeitbestätigungen PDF");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "DocumentsOnPage", "Documents on page:");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "DocumentsOnPage", "Anzahl Dokumente auf der Seite:");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "EmployeeName", "Employee name");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "EmployeeName", "Mitarbeitername");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "EmployeeName", "Mitarbeitername");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "Created", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "Created", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "Receipt", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "Receipt", "Belegnummer");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "Receipt", "Belegnummer");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "Status", "Status");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "Status", "Status");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "Preview", "Preview");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "Preview", "Vorschau");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "Preview", "Vorschau");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "PDF", "PDF");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "PDF", "PDF");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "PDF", "PDF");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "DownloadPDF", "Download PDF");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "DownloadPDF", "Download PDF");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "DownloadPDF", "Download PDF");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "RecreationConfirmation", "Recreation confirmation");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "RecreationConfirmation", "Bestätigung der Erholung");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "RecreationConfirmation", "Bestätigung der Erholung");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "RecreationConfirmationList", "Confirmation list");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "RecreationConfirmationList", "Bestätigungsliste");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "RecreationConfirmationList", "Bestätigungsliste");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "History", "View history");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "History", "Historie anzeigen");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "History", "Historie anzeigen");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "UpdatedAt", "Updated at");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "UpdatedAt", "Aktualisiert am");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "UpdatedAt", "Aktualisiert am");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "EditConfirmation", "Edit confirmation PDF");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "EditConfirmation", "Bestätigungs-PDF bearbeiten");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "EditConfirmation", "Bestätigungs-PDF bearbeiten");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "confirmation_list.html", "CreateConfirmation", "Create confirmation PDF");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "confirmation_list.html", "CreateConfirmation", "Bestätigungs-PDF erstellen");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "confirmation_list.html", "CreateConfirmation", "Bestätigungs-PDF erstellen");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "partial_confirmation_history_list.html", "ConfirmationHistory", "Confirmation history");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "partial_confirmation_history_list.html", "ConfirmationHistory", "Bestätigungsverlauf");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "partial_confirmation_history_list.html", "ConfirmationHistory", "Bestätigungsverlauf");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "partial_confirmation_history_list.html", "User", "User");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "partial_confirmation_history_list.html", "User", "Nutzer");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "partial_confirmation_history_list.html", "User", "Nutzer");

        $this->langVarList[] = new LangVar("en", "template", "agreements", "partial_confirmation_history_list.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "partial_confirmation_history_list.html", "Created", "Erstellungsdatum");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "partial_confirmation_history_list.html", "Created", "Erstellungsdatum");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-end_date", "End Date Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-end_date", "Enddatum Sachlohnart");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-end_date", "Enddatum Sachlohnart");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-confirmation_message", "Confirmation message");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-confirmation_message", "Bestätigungsmeldung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-confirmation_message", "Bestätigungsmeldung");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-confirmation_transaction_message", "Confirmation transaction message");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-confirmation_transaction_message", "Bestätigungs-Transaktionsnachricht");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-confirmation_transaction_message", "Bestätigungs-Transaktionsnachricht");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-material_status", "Material status");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-material_status", "Familienstand");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-material_status", "Familienstand");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-child_count", "Child count");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-child_count", "Anzahl Kinder im Haushalt");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-child_count", "Anzahl Kinder im Haushalt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "material-status-married", "verheiratet");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "material-status-married", "verheiratet");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "material-status-married", "verheiratet");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "material-status-single", "ledig");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "material-status-single", "ledig");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "material-status-single", "ledig");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-section-confirmation", "Recreation confirmation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-section-confirmation", "Recreation confirmation");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-section-confirmation", "Recreation confirmation");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-confirmation_id_download", "View employee confirmation PDF");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-confirmation_id_download", "PDF zur Mitarbeiterbestätigung anzeigen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-confirmation_id_download", "PDF zur Mitarbeiterbestätigung anzeigen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-confirmation_id_save", "Save confirmation content");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-confirmation_id_save", "Bestätigungsinhalt speichern");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-confirmation_id_save", "Bestätigungsinhalt speichern");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-confirmation_id", "View confirmation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-confirmation_id", "Bestätigung anzeigen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-confirmation_id", "Bestätigung anzeigen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-confirmation_list", "View confirmation list");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-confirmation_list", "Bestätigungsliste anzeigen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-confirmation_list", "Bestätigungsliste anzeigen");
    }

    public function up()
    {
        $this->table("recreation_confirmation", ["id" => "confirmation_id"])
            ->addColumn("company_unit_id", "integer", ["null" => false])
            ->addColumn("updated_at", "timestamp", ["null" => false])
            ->addColumn("content", "text", ["null" => true])
            ->save();

        $this->table("recreation_confirmation_employee", ["id" => "id"])
            ->addColumn("confirmation_id", "integer", ["null" => false])
            ->addColumn("employee_id", "integer", ["null" => false])
            ->addColumn("receipt_id", "integer", ["null" => false])
            ->addColumn("pdf_file", "string", ["length" => 255, "null" => true])
            ->addColumn("status", "string", ["length" => 255, "null" => true])
            ->addColumn("created", "timestamp", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->dropTable("recreation_confirmation");

        $this->dropTable("recreation_confirmation_employee");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
