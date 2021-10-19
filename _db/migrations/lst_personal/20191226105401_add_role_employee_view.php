<?php

use Phinx\Migration\AbstractMigration;

class AddRoleEmployeeView extends AbstractMigration
{
    public function change()
    {
        $stmt = GetStatement(DB_CONTROL);
        $roleMobileUser = $this->fetchRow("SELECT permission_id FROM permission WHERE name='api'");
        $roleEmployeeView = $this->fetchRow("SELECT permission_id FROM permission WHERE name='employee_view'");

        $mobileUserList = $this->fetchAll("SELECT user_id FROM user_permissions WHERE permission_id=".Connection::GetSQLString($roleMobileUser["permission_id"]));

        foreach ($mobileUserList as $user)
        {
            $hasEmployeeView = $this->fetchRow("SELECT user_id FROM user_permissions WHERE permission_id=".Connection::GetSQLString($roleEmployeeView["permission_id"])." AND user_id=".Connection::GetSQLString($user["user_id"]));
            if ($hasEmployeeView == false)
            {
                $this->execute("INSERT INTO user_permissions (user_id, permission_id, link_id)
                VALUES(".Connection::GetSQLString($user["user_id"]).", ".Connection::GetSQLString($roleEmployeeView["permission_id"]).", NULL)");

                $query = "INSERT INTO user_permission_history (end_user_id, permission_id, value, created, start_user_id, created_from)
					VALUES (
					" . Connection::GetSQLString($user["user_id"]) . ",
					" . Connection::GetSQLString($roleEmployeeView["permission_id"]) . ",
                    'Y',
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
					" . Connection::GetSQLString(SERVICE_USER_ID) . ",
					'admin')
					RETURNING value_id";
                $stmt->Execute($query);
            }
        }
    }
}
