<?php

use Phinx\Migration\AbstractMigration;

class FoodValueLevels extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET
                  level_company_unit = 'Y',
                  level_employee = 'Y'
                  WHERE code = " . Connection::GetSQLString(OPTION__FOOD__MAIN__MEAL_VALUE) .
            " OR code = " . Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE));
    }

    public function down()
    {
        $this->execute("UPDATE option SET
                  level_company_unit = 'N',
                  level_employee = 'N'
                  WHERE code = " . Connection::GetSQLString(OPTION__FOOD__MAIN__MEAL_VALUE) .
            " OR code = " . Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE));
    }
}
