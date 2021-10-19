<?php


use Phinx\Migration\AbstractMigration;

class EmployeeAgreemetsFileName extends AbstractMigration
{
    public function up()
    {
        $this->table("agreements_employee")
        ->addColumn("file", "string", ["length" => 255, "null" => true])
        ->save();
        
    }
    
    public function down()
    {
        $this->table("agreements_employee")
        ->removeColumn("file")
        ->save();
    }
}
