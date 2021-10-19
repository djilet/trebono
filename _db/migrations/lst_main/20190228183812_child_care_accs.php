<?php


use Phinx\Migration\AbstractMigration;

class ChildCareAccs extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")
        ->addColumn("acc_child_care_tax_free", "string", ["length" => 255, "null" => true])
        ->save(); 
    }
    
    public function down()
    {
        $this->table("company_unit")
        ->removeColumn("acc_child_care_tax_free")
        ->save();
    }
}
