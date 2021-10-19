<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherSepa extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-group-benefit_voucher", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-group-benefit_voucher", "Sachbezug Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-group-benefit_voucher", "Sachbezug Gutschein");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "VoucherExport", "Export Vouchers (Invoices)");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "VoucherExport", "Gutscheine exportieren (Rechnungen)");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "VoucherExport", "Gutscheine exportieren (Rechnungen)");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "CreditorExport", "Creditor export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "CreditorExport", "Gläubigerexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "CreditorExport", "Gläubigerexport");


    }

    public function up()
    {
        $this->table("voucher_export_datev", ["id" => "export_id"])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("export_number", "integer", ["null" => false])
            ->addColumn("type", "string", ["null" => true])
            ->save();

        $this->table("voucher")
            ->addColumn("debitor_export_id", "integer", ["null" => true])
            ->addColumn("creditor_export_id", "integer", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->dropTable("voucher_export_datev");

        $this->table("voucher")
            ->removeColumn("debitor_export_id")
            ->removeColumn("creditor_export_id")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
