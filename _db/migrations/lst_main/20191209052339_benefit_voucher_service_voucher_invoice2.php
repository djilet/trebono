<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherServiceVoucherInvoice2 extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")->addColumn("invoice_export_id", "string", ["length" => 255, "null" => true])->save();
    }

    public function down()
    {
        $this->table("voucher")->removeColumn("invoice_export_id");
    }
}
