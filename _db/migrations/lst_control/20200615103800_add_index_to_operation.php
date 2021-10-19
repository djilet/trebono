<?php

use Phinx\Migration\AbstractMigration;

class AddIndexToOperation extends AbstractMigration
{
    public function up()
    {
        $this->table("operation")
            ->addIndex(["user_id", "object_id"], ["name" => "user_object_id_index"])
            ->save();
    }

    public function down()
    {
        $this->table("operation")
            ->removeIndexByName("user_object_id_index")
            ->save();
    }
}
