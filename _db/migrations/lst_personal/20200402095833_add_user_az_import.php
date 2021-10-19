<?php

use Phinx\Migration\AbstractMigration;

class AddUserAzImport extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO user_info (user_id, email, password, salutation, first_name, last_name, created, archive) 
						VALUES (
						    '-3',
                            'fineasy.import@2kscs.de',
                            '',
							'Frau',
							".Connection::GetSQLEncryption("'AZ file'").",
							".Connection::GetSQLEncryption("'import'").",
							".Connection::GetSQLString(GetCurrentDateTime()).",					
							'Y'
						)");
    }

    public function down()
    {
        $this->execute("DELETE FROM user_info WHERE user_id='-3'");
    }
}
