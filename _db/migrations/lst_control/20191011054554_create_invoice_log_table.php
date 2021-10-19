<?php

use Phinx\Migration\AbstractMigration;

class CreateInvoiceLogTable extends AbstractMigration
{
    public function up()
    {
        $this->table("invoice_history", ["id" => "value_id"])
            ->addColumn("invoice_id", "integer", ["null" => false])
            ->addColumn("property_name", "text", ["null" => false])
            ->addColumn("value", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("created_from", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->dropTable("invoice_history");
    }
}
