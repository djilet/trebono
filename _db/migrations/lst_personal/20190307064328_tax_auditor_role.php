<?php


use Phinx\Migration\AbstractMigration;

class TaxAuditorRole extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO permission(
			permission_id, name, title, link_to)
			VALUES (9, 'tax_auditor', 'Tax auditor', 'company_unit')");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM user_permissions WHERE permission_id=9");
        $this->execute("DELETE FROM permission WHERE name='tax_auditor'");
    }
}
