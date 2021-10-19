<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherTestingInvoices extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "CreateVoucherInvoice", "Create voucher invoice");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "CreateVoucherInvoice", "Gutscheinrechnung erstellen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "CreateVoucherInvoice", "Gutscheinrechnung erstellen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreview", "Invoice voucher preview");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreview", "Gutscheinrechnungsvorschau");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreview", "Gutscheinrechnungsvorschau");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewDate", "Invoice voucher date");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewDate", "Gutscheinrechnungsdatum");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewDate", "Gutscheinrechnungsdatum");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewAfter", "Current month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewAfter", "Gutscheinrechnung aktuellen Monat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewAfter", "Gutscheinrechnung aktuellen Monat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewBefore", "Previous month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewBefore", "Gutscheinrechnung Vormonat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewBefore", "Gutscheinrechnung Vormonat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewNoID", "Cannot create invoice voucher for non existing company");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewNoID", "Gutscheinrechnung für nicht vorhandenes Unternehmen kann nicht erstellt werden");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewNoID", "Gutscheinrechnung für nicht vorhandenes Unternehmen kann nicht erstellt werden");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewEmptyDate", "Please, select date for invoice voucher");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewEmptyDate", "Bitte wählen Sie das Gutscheinrechnung aus");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoiceVoucherPreviewEmptyDate", "Bitte wählen Sie das Gutscheinrechnung aus");
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
