<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherFixCreditorExport extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt")
            ->addColumn("creditor_export_id", "integer", ["null" => true])
            ->save();

        $this->table("voucher")
            ->removeColumn("creditor_export_id")
            ->save();
    }

    public function down()
    {
        $this->table("receipt")
            ->removeColumn("creditor_export_id")
            ->save();

        $this->table("voucher")
            ->addColumn("creditor_export_id", "integer", ["null" => true])
            ->save();
    }
}
