<?php

use Phinx\Migration\AbstractMigration;

class TravelLanMealAllowance extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->removeColumn("lan_meal_allowance")
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->addColumn("lan_meal_allowance", "string", ["length" => 255, "null" => true])
            ->save();
    }
}
