<?php

use Phinx\Migration\AbstractMigration;

class AddContractRole extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission (permission_id, name, title, link_to, group_id, sort_order) 
            VALUES (18, 'contract', 'Contract', 'company_unit', 3, 18)");
    }

    public function down()
    {
        $permissionId = $this->fetchRow("SELECT permission_id FROM permission WHERE name='contract'");
        $userPermissionIDs = $this->fetchRow("SELECT user_permission_id FROM user_permissions 
            WHERE permission_id=".$permissionId["permission_id"]);
        if($userPermissionIDs)
            $this->execute("DELETE FROM user_permissions 
            WHERE user_permission_id IN (".implode(',',$userPermissionIDs).")");

        $this->execute("DELETE FROM permission WHERE name='contract'");
    }
}
