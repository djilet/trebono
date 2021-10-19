<?php


use Phinx\Migration\AbstractMigration;

class CreateBillingSystemUser extends AbstractMigration
{
    public function up(){
        $this->execute("INSERT INTO user_info(
	user_id, email, password, first_name, created, last_name, archive)
	VALUES (-1, 'fineasy@2kscs.de', '', 'Billing', '2018-09-01', 'System', 'Y')");
    }
    public function down(){
        $this->execute("DELETE FROM user_info WHERE user_id='-1'");
    }
}
