<?php

use Phinx\Migration\AbstractMigration;

class VoucherReceiptIndex extends AbstractMigration
{
    public function up()
    {
        $this->execute("create index if not exists voucher_receipt_index on voucher_receipt (voucher_id, receipt_id, amount)");
    }

    public function down()
    {
        $this->execute("drop index if exists voucher_receipt_index");
    }
}
