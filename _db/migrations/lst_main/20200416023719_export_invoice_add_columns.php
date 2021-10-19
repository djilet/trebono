<?php

use Phinx\Migration\AbstractMigration;

class ExportInvoiceAddColumns extends AbstractMigration
{
    public function up()
    {
        $this->table("invoice_export_datev")
            ->addColumn("date_from", "timestamp", ["null" => true])
            ->addColumn("date_to", "timestamp", ["null" => true])
            ->addColumn("type", "string", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("invoice_export_datev")
            ->removeColumn("date_from")
            ->removeColumn("date_to")
            ->removeColumn("type")
            ->save();
    }
}
