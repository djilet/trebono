<?php

use Phinx\Migration\AbstractMigration;

class UpdateInvoiceLineTable extends AbstractMigration
{
    public function up()
    {
        $this->table("invoice_line")
            ->addColumn("billable_item_id", "integer", ["null" => true, "default" => null])
            ->save();
    }

    public function down()
    {
        $this->table("invoice_line")
            ->removeColumn("billable_item_id")
            ->save();
    }
}
