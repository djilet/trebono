<?php

use Phinx\Migration\AbstractMigration;

class AddSortOrderToPermissionTable extends AbstractMigration
{
    public function change()
    {
        $this->table("permission")
            ->addColumn("sort_order", "integer", ["limit" => 4, "default" => 1])
            ->update();
    }
}
