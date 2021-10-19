<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherApprovedReceiptPayment extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value, created_from) VALUES (
            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
            0,
            ".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED).",
            NOW(),
            ".BILLING_USER_ID.",
            'N',
            'admin'
            )");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED));
    }
}
