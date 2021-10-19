<?php

use Phinx\Migration\AbstractMigration;

class RecreationChangesPdfHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("recreation_confirmation_history", ["id" => "value_id"])
            ->addColumn("confirmation_id", "integer", ["null" => true])
            ->addColumn("content", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => true])
            ->addColumn("created", "timestamp", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->dropTable("recreation_confirmation_history");
    }
}
