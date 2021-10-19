<?php

use Phinx\Migration\AbstractMigration;

class FixAttributForSepaServiceDate extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")
            ->changeColumn("sepa_service_date", "timestamp", ["null" => true, "default" => "2020-01-01 00:00:00"])
            ->update();
    }

    public function down()
    {
        $this->table("company_unit")
            ->changeColumn("sepa_service_date", "timestamp", ["null" => false, "default" => "2020-01-01 00:00:00"])
            ->update();
    }
}
