<?php


use Phinx\Migration\AbstractMigration;

class ApiWebPermission extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission(
			permission_id, name, title, link_to)
			VALUES (11, 'webapi', 'Web administrator', '')");
    }

    public function down()
    {
        $this->execute("DELETE FROM user_permissions WHERE permission_id=11");
        $this->execute("DELETE FROM permission WHERE name='webapi'");
    }
}
