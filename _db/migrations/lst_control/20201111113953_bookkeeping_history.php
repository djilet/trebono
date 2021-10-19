<?php

use Phinx\Migration\AbstractMigration;

class BookkeepingHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("bookkeeping_export_history", ["id" => "value_id"])
            ->addColumn("bookkeeping_export_id", "integer", ["null" => false])
            ->addColumn("property_name", "text", ["null" => false])
            ->addColumn("value", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("created_from", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->table("bookkeeping_export_history")
        ->drop()
        ->save();
    }
}
