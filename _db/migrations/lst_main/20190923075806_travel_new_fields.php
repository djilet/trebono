<?php

use Phinx\Migration\AbstractMigration;

class TravelNewFields extends AbstractMigration
{

    public function up()
    {
        $this->table("trip")
            ->addColumn("purpose", "string", ["limit" => 255, "null" => true])
            ->addColumn("start_date", "datetime", ["limit" => 255, "null" => true])
            ->addColumn("end_date", "datetime", ["limit" => 255, "null" => true])
            ->update();
    }

    public function down()
    {
        $this->table("payroll")
            ->removeColumn("purpose")
            ->removeColumn("start_date")
            ->removeColumn("end_date")
            ->update();
    }
}
