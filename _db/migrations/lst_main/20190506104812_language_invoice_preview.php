<?php


use Phinx\Migration\AbstractMigration;

class LanguageInvoicePreview extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Preview", "Preview");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Preview", "Vorschau");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Preview", "Vorschau");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreview", "Invoice preview");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreview", "Rechnungsvorschau");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreview", "Rechnungsvorschau");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreviewDate", "Invoice date");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreviewDate", "Rechnungsdatum");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreviewDate", "Rechnungsdatum");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreviewAfter", "Invoice for current month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreviewAfter", "Rechnung für den aktuellen Monat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreviewAfter", "Rechnung für den aktuellen Monat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreviewBefore", "Invoice for previous month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreviewBefore", "Rechnung für den Vormonat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreviewBefore", "Rechnung für den Vormonat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreviewNoID", "Cannot create invoice for non existing company");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreviewNoID", "Rechnung für nicht vorhandenes Unternehmen kann nicht erstellt werden");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreviewNoID", "Rechnung für nicht vorhandenes Unternehmen kann nicht erstellt werden");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "InvoicePreviewEmptyDate", "Please, select date for invoice");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "InvoicePreviewEmptyDate", "Bitte wählen Sie das Rechnungsdatum aus");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "InvoicePreviewEmptyDate", "Bitte wählen Sie das Rechnungsdatum aus");
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
