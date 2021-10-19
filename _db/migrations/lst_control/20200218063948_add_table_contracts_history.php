<?php

use Phinx\Migration\AbstractMigration;

class AddTableContractsHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("contract_history", ["id" => "value_id"])
            ->addColumn("level", "string", ["null" => false])
            ->addColumn("contract_id", "integer", ["null" => false])
            ->addColumn("property_name", "string", ["null" => false])
            ->addColumn("value", "string", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->dropTable("contract_history");
    }
}
