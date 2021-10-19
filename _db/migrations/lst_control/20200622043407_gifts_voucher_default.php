<?php

use Phinx\Migration\AbstractMigration;

class GiftsVoucherDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'V',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '1',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '10',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '60',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '6',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'Y',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT));
    }
}
