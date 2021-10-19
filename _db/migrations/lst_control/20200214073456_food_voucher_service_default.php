<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherServiceDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            'V',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '10',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '15',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '120',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            'Y',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            'N',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '3.4',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '3.1',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '3.3',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH));

        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT));

        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT));
    }
}
