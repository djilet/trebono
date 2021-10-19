<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementMonthlyLimitDefault extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            '50',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY));
    }
}
