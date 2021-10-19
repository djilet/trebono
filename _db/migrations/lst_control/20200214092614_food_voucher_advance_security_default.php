<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherAdvanceSecurityDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '2',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE).",
            '2020-01-01 00:00:00',
            ".BILLING_USER_ID.",
            '10',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__MONTHLY_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__ADVANCED_SECURITY__IMPLEMENTATION_PRICE));
    }
}
