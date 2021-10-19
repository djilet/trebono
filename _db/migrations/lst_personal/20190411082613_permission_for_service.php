<?php


use Phinx\Migration\AbstractMigration;

class PermissionForService extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission(
			permission_id, name, title, link_to)
			VALUES (10, 'service', 'Service administrator', 'product_group')");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM user_permissions WHERE permission_id=10");
        $this->execute("DELETE FROM permission WHERE name='service'");
    }
}
