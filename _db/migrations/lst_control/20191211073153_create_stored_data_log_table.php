<?php

use Phinx\Migration\AbstractMigration;

class CreateStoredDataLogTable extends AbstractMigration
{
    public function up()
    {
        $this->table("stored_data_history", ["id" => "value_id"])
            ->addColumn("stored_data_id", "integer", ["null" => false])
            ->addColumn("property_name", "text", ["null" => false])
            ->addColumn("value", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("created_from", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->dropTable("stored_data_history");
    }
}
