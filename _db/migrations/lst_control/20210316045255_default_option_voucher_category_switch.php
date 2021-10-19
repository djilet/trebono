<?php

use Phinx\Migration\AbstractMigration;

class DefaultOptionVoucherCategorySwitch extends AbstractMigration
{
    public function up()
    {
        //commented out because now the default value should be empty
        /*$this->execute("INSERT INTO option_value_history (level, entity_id, option_id, date_from, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE).",
            '2020-01-01 00:00:00',
            NOW(),
            ".BILLING_USER_ID.",
            'N',
            'admin'
            )");*/
    }

    public function down()
    {
        //$this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE));
    }
}
