<?php

use Phinx\Migration\AbstractMigration;

class AddUserForAutoAdoption extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO user_info (user_id, email, password, salutation, first_name, last_name, created, archive) 
                            VALUES
                                ('-12','fineasy.autoadoption@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Auto Adoption'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y')
                            ");
    }

    public function down()
    {
        $this->execute("DELETE FROM user_info WHERE user_id='-12'");
    }
}
