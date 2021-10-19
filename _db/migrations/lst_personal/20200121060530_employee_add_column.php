<?php

use Phinx\Migration\AbstractMigration;

class EmployeeAddColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")->addColumn("master_data_export_id", "integer", ["null" => true])->save();
    }

    public function down()
    {
        $this->table("employee")->removeColumn("master_data_export_id");
    }
}
