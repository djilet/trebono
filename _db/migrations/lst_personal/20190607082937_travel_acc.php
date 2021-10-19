<?php


use Phinx\Migration\AbstractMigration;

class TravelAcc extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
        ->addColumn("acc_travel_tax_free", "string", ["length" => 255, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("employee")
        ->removeColumn("acc_travel_tax_free")
        ->save();
    }
}
