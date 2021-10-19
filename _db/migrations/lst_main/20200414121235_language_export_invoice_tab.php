<?php

use Phinx\Migration\AbstractMigration;

class LanguageExportInvoiceTab extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportInvoiceList", "Export invoice list");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportInvoiceList", "Rechnungsliste exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportInvoiceList", "Rechnungsliste exportieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportInvoicesOnPage", "Export invoices on page:");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportInvoicesOnPage", "Rechnungen exportieren auf Seite:");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportInvoicesOnPage", "Rechnungen exportieren auf Seite:");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportInvoiceType", "Export invoice type");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportInvoiceType", "Rechnungsart exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportInvoiceType", "Rechnungsart exportieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportFile", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportFile", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportFile", "Export");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "CreatedUser", "Created user");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "CreatedUser", "Benutzer erstellt");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "CreatedUser", "Benutzer erstellt");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportInvoiceVoucherType", "Export Voucher Invoices");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportInvoiceVoucherType", "Gutscheinrechnungen exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportInvoiceVoucherType", "Gutscheinrechnungen exportieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ExportInvoiceServiceType", "Export Service Invoices");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ExportInvoiceServiceType", "Servicerechnungen exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ExportInvoiceServiceType", "Servicerechnungen exportieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "DownloadCSV", "Download CSV");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "DownloadCSV", "Herunterladen CSV");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "DownloadCSV", "Herunterladen CSV");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
