<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherAdditionalInvoice extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoiceType", "Invoice type");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoiceType", "");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoiceType", "");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoiceVoucher", "Voucher invoice");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoiceVoucher", "Gutschein Rechnung");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoiceVoucher", "Gutschein Rechnung");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoicePlain", "Invoice");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoicePlain", "Rechnung");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoicePlain", "Rechnung");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__main", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-benefit_voucher__main", "Sachbezug Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-benefit_voucher__main", "Sachbezug Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-benefit_voucher__advanced_security", "Benefit Voucher Service Advanced Security");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-benefit_voucher__advanced_security", "Sachbezug Gutschein Erweiterte Sicherheit");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-benefit_voucher__advanced_security", "Sachbezug Gutschein Erweiterte Sicherheit");
    }

    public function up()
    {
        $this->table("invoice")
            ->addColumn("invoice_type", "string", ["length" => 255, "null" => true])
            ->save();

        $this->execute("UPDATE invoice SET invoice_type='invoice'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("invoice")
            ->removeColumn("invoice_type")
            ->save();

        $this->execute("DELETE FROM invoice WHERE invoice_type='voucher_invoice'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
