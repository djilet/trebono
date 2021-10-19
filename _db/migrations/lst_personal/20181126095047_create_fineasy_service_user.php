<?php


use Phinx\Migration\AbstractMigration;

class CreateFineasyServiceUser extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO user_info(
			user_id, email, password, first_name, created, last_name, archive)
			VALUES (-2, 'fineasy.service@2kscs.de', '', 'FIN-easy', '2018-09-01', 'Service', 'Y')");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM user_info WHERE user_id='-2'");
    }
}
