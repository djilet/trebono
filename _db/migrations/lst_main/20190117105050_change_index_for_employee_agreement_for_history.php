<?php


use Phinx\Migration\AbstractMigration;

class ChangeIndexForEmployeeAgreementForHistory extends AbstractMigration
{
    public function up()
    {        
        $this->table("agreements_employee")
        ->changePrimaryKey(["agreement_id", "employee_id", "version"])
        ->save();
    }
    
    
    public function down()
    {
        $this->table("agreements_employee")
        ->changePrimaryKey(["agreement_id", "employee_id"])
        ->save();
    }
}
