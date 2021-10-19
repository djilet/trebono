<?php

use Phinx\Migration\AbstractMigration;

class TravelLanMealAllowance extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")
            ->removeColumn("lan_meal_allowance")
            ->save();
    }

    public function down()
    {
        $this->table("company_unit")
            ->addColumn("lan_meal_allowance", "string", ["length" => 255, "null" => true])
            ->save();
    }
}
