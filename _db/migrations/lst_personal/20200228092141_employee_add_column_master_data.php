<?php

use Phinx\Migration\AbstractMigration;

class EmployeeAddColumnMasterData extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")->addColumn("master_data_export_update_id", "integer", ["null" => true, "default" => 0])->save();
    }

    public function down()
    {
        $this->table("employee")->removeColumn("master_data_export_update_id")->save();
    }
}
