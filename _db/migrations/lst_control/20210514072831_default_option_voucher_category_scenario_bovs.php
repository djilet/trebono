<?php

use Phinx\Migration\AbstractMigration;

class DefaultOptionVoucherCategoryScenarioBovs extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'exchangeable',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO));
    }
}
