<?php


use Phinx\Migration\AbstractMigration;

class EndDateReccurencyVoucher extends AbstractMigration
{
    public function up()
    {
        $this->table("voucher")
        ->addColumn("recurring_end_date", "date", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("voucher")
        ->removeColumn("recurring_end_date")
        ->save();
    }
}
