<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class AddRoleDataExportReceiver extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission (permission_id, name, title, link_to) 
						VALUES (
						    15,
						    'stored_data',
                            'Data export receiver',
                            'company_unit'
						)");

        $this->table("contact")
            ->addColumn("contact_for_stored_data", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->addColumn("contact_for_company_unit_admin", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->addColumn("contact_for_employee_admin", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }

    public function down()
    {
        $userPermissionIDs = $this->fetchRow("SELECT user_permission_id FROM user_permissions WHERE permission_id=15");
        if($userPermissionIDs)
            $this->execute("DELETE FROM user_permissions WHERE user_permission_id IN (".implode(',',$userPermissionIDs).")");

        $this->execute("DELETE FROM permission WHERE name='stored_data'");

        $this->table("contact")
            ->removeColumn("contact_for_stored_data")
            ->removeColumn("contact_for_company_unit_admin")
            ->removeColumn("contact_for_employee_admin")
            ->save();
    }
}
