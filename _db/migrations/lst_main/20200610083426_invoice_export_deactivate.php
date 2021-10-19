<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class InvoiceExportDeactivate extends AbstractMigration
{
    public function up()
    {
        $this->table("invoice_export_datev")
            ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }

    public function down()
    {
        $this->table("invoice_export_datev")
            ->removeColumn("archive")
            ->save();
    }
}
