<?php

use Phinx\Migration\AbstractMigration;

class EmployeeTravelFields extends AbstractMigration
{

    public function up()
    {

        $this->table("employee")
            ->addColumn("lan_meal_allowance", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_daily_allowance", "string", ["length" => 255, "null" => true])

            ->addColumn("acc_ticket", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_accommodation", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_corporate_hospitality", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_parking", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_other_costs", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_travel_costs", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_creditor", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("lan_meal_allowance")
            ->removeColumn("acc_daily_allowance")

            ->removeColumn("acc_ticket")
            ->removeColumn("acc_accommodation")
            ->removeColumn("acc_corporate_hospitality")
            ->removeColumn("acc_parking")
            ->removeColumn("acc_other_costs")
            ->removeColumn("acc_travel_costs")
            ->removeColumn("acc_creditor")
            ->save();
    }
}
