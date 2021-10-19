<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementAccField extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->addColumn("acc_corporate_health_management", "string", ["length" => 255, "null" => true, "after" => "acc_gift"])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("acc_corporate_health_management")
            ->save();
    }
}
