<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherServiceOptions extends AbstractMigration
{

    public function up()
    {
        $this->execute("UPDATE option SET level_company_unit='N', level_employee='N'
                    WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT));

        $this->execute("UPDATE option SET level_company_unit='Y', level_employee='Y'
                    WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION));
    }

    public function down()
    {
        $this->execute("UPDATE option SET level_company_unit='N', level_employee='N'
                    WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION));

        $this->execute("UPDATE option SET level_company_unit='Y', level_employee='Y'
                    WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT));
    }
}
