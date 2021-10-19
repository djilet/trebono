<?php

use Phinx\Migration\AbstractMigration;

class CreateMasterDataTable extends AbstractMigration
{
    public function up()
    {
        $this->table("master_data", ["id" => "master_data_id"])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("type", "string", ["length" => 255])
            ->save();
    }

    public function down()
    {
        $this->dropTable("master_data");
    }
}
