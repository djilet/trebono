<?php

use Phinx\Migration\AbstractMigration;

class EmployeeActiveContractNumber extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->addColumn("active_contract_number", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("active_contract_number")
            ->save();
    }
}
