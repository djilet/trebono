<?php

use Phinx\Migration\AbstractMigration;

class FoodFlexOptionDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_OPTION).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'N',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '1',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_FREE_UNITS).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");

        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '5',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_OPTION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_FREE_UNITS));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE));
    }

}
