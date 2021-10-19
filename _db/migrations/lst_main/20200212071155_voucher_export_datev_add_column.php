<?php

use Phinx\Migration\AbstractMigration;

class VoucherExportDatevAddColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher_export_datev")->addColumn("export_month", "string", ["null" => true])->save();
    }

    public function down()
    {
        $this->table("voucher_export_datev")->removeColumn("export_month")->save();
    }
}
