<?php


use Phinx\Migration\AbstractMigration;

class TransportEmployeeAcc extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
        ->addColumn("acc_transport_tax_free", "string", ["length" => 255, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("employee")
        ->removeColumn("acc_transport_tax_free")
        ->save();
    }
}
