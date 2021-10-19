<?php


use Phinx\Migration\AbstractMigration;

class PayrollRole extends AbstractMigration
{
    public function up(){
        $query = "INSERT INTO permission (permission_id, name, title, link_to)
                    VALUES(8, 'payroll', 'Payroll receiver', 'company_unit')";
        $this->execute($query);
    }

    public function down(){
        $query = "DELETE FROM permission WHERE name='payroll'";
        $this->execute($query);
    }
}
