<?php


use Phinx\Migration\AbstractMigration;

class BonusVoucherEndDate extends AbstractMigration
{
    public function up()
    {   
        $this->table("voucher")
        ->addColumn("end_date", "date", ["null" => true])
        ->save();
        
        $voucherList = $this->fetchAll("SELECT * FROM voucher");
        foreach($voucherList as $key => $voucher)
        {
            $endDate = date("Y-12-31", strtotime($voucher["voucher_date"]));
            $this->execute("UPDATE voucher SET end_date='".$endDate."' WHERE voucher_id=".$voucher["voucher_id"]);
        }
        
        $this->table("voucher")
        ->changeColumn("end_date", "date", ["null" => false])
        ->save();
    }
    
    public function down()
    {
        $this->table("voucher")
        ->removeColumn("end_date")
        ->save();
    }
}
