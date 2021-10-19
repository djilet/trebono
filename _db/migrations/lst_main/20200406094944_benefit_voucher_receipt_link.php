<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherReceiptLink extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher_receipt")
            ->addColumn("amount", "float", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("voucher_receipt")
            ->removeColumn("amount")
            ->save();
    }
}
