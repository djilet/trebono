<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherSepaDebitor extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
            ->removeColumn("debitor_export_id")
            ->save();

        $this->table("voucher_export_datev")
            ->removeColumn("type")
            ->save();
    }

    public function down()
    {
        $this->table("voucher")
            ->addColumn("debitor_export_id", "integer", ["null" => true])
            ->save();

        $this->table("voucher_export_datev")
            ->addColumn("type", "string", ["null" => true])
            ->save();
    }
}
