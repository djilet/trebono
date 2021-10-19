<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'Z',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '600',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'Y',
            'admin'
            )");


        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '1',
            'admin'
            )");
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE).",
            '2020-06-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '10',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION));
    }
}
