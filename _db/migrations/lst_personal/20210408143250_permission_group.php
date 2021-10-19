<?php

use Phinx\Migration\AbstractMigration;

class PermissionGroup extends AbstractMigration
{
    public function change()
    {
        $this->table("permission_group", ["id" => "group_id", "limit" => 4])
            ->addColumn("sort_order", "integer", ["limit" => 4, "default" => 1])
            ->addColumn("code", "string", ["limit" => 255])
            ->create();
    }
}
