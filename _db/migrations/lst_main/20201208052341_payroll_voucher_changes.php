<?php

use Phinx\Migration\AbstractMigration;

class PayrollVoucherChanges extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
            ->addColumn("datev_export", "string", ["length" => 255, "default" => '0'])
            ->addColumn("pdf_export", "string", ["length" => 255, "default" => '0'])
            ->save();

        /*$this->execute("UPDATE voucher SET datev_export='', pdf_export=''
                            WHERE voucher_date < '01.12.2020'");*/
    }

    public function down()
    {
        $this->table("voucher")
            ->removeColumn("datev_export")
            ->removeColumn("pdf_export")
            ->save();
    }
}
