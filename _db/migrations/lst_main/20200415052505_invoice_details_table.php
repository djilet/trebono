<?php

use Phinx\Migration\AbstractMigration;

class InvoiceDetailsTable extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "InvoiceDetails", "Invoice details");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "InvoiceDetails", "Rechnungs-Details");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "InvoiceDetails", "Rechnungs-Details");

        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "DownloadInvoiceDetails", "Download invoice details");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "DownloadInvoiceDetails", "Rechnungsdetails herunterladen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "DownloadInvoiceDetails", "Rechnungsdetails herunterladen");
    }
    public function up()
    {
        $this->table("invoice_details", ["id" => "invoice_details_id"])
            ->addColumn("invoice_id", "integer", ["null" => false])
            ->addColumn("employee_id", "integer", ["null" => false])
            ->addColumn("company_unit_id", "integer", ["null" => false])
            ->addColumn("product_id", "integer", ["null" => false])
            ->addColumn("type", "string", ["null" => false])
            ->addColumn("days_count", "integer", ["null" => true])
            ->addColumn("cost", "decimal", ["null" => true, "precision" => 10, "scale" => 2])
            ->addColumn("voucher_ids", "text", ["null" => true])
            ->save();

        $this->table("invoice")
            ->addColumn("details_file", "text", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->dropTable("invoice_details");

        $this->table("invoice")
            ->removeColumn("details_file")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
