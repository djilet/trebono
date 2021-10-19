<?php

use Phinx\Migration\AbstractMigration;

class AddGroupIdToPermissionTable extends AbstractMigration
{
    public function change()
    {
        $this->table("permission")
            ->addColumn("group_id", "integer", ["limit" => 4, "null" => true])
            ->addForeignKey("group_id", "permission_group", "group_id", ["delete" => "RESTRICT", "update" => "CASCADE"])
            ->update();
    }
}
