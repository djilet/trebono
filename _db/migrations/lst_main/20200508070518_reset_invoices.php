<?php

use Phinx\Migration\AbstractMigration;

class ResetInvoices extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ResetVoucherInvoice", "Reset voucher invoice");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ResetVoucherInvoice", "Gutscheinrechnung zurücksetzen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ResetVoucherInvoice", "Gutscheinrechnung zurücksetzen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ResetInvoiceExport", "Reset invoice export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ResetInvoiceExport", "Rechnungsexport zurücksetzen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ResetInvoiceExport", "Rechnungsexport zurücksetzen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "ResetVoucherInvoiceExport", "Reset voucher invoice export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "ResetVoucherInvoiceExport", "Export der Gutscheinrechnung zurücksetzen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "ResetVoucherInvoiceExport", "Export der Gutscheinrechnung zurücksetzen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoiceExportCreated", "Invoice export created");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoiceExportCreated", "Rechnungsexport erstellt");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoiceExportCreated", "Rechnungsexport erstellt");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoiceVoucherExportCreated", "Invoice voucher export created");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoiceVoucherExportCreated", "Rechnungsbeleg-Export erstellt");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoiceVoucherExportCreated", "Rechnungsbeleg-Export erstellt");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "VoucherResetFieldsNotFilled", "Both date and company unit must be selected for reset");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "VoucherResetFieldsNotFilled", "Sowohl Datum als auch Firmeneinheit müssen zum Zurücksetzen ausgewählt werden");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "VoucherResetFieldsNotFilled", "Sowohl Datum als auch Firmeneinheit müssen zum Zurücksetzen ausgewählt werden");
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
