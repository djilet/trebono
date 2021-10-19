<?php

use Phinx\Migration\AbstractMigration;

class GiftEmployeeNewAcc extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->addColumn("acc_gift", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("acc_gift")
            ->save();
    }
}
