<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherEmployeeRole extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission(
			permission_id, name, title, link_to)
			VALUES (13, 'employee_view', 'Employee', 'employee')");
    }

    public function down()
    {
        $this->execute("DELETE FROM user_permissions WHERE permission_id=13");
        $this->execute("DELETE FROM permission WHERE name='employee_view'");
    }
}
