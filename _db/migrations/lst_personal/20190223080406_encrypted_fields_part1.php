<?php


use Phinx\Migration\AbstractMigration;

class EncryptedFieldsPart1 extends AbstractMigration
{
    public function up()
    {
    	$this->execute("CREATE EXTENSION IF NOT EXISTS pgcrypto");
    	
    	$this->execute("ALTER TABLE user_info ALTER COLUMN first_name TYPE text");
    	$this->execute("ALTER TABLE user_info ALTER COLUMN last_name TYPE text");
    	$this->execute("ALTER TABLE user_info ALTER COLUMN street TYPE text");
    	
    	$this->execute("UPDATE user_info SET first_name=".Connection::GetSQLEncryption("first_name"));
    	$this->execute("UPDATE user_info SET last_name=".Connection::GetSQLEncryption("last_name"));
    	$this->execute("UPDATE user_info SET street=".Connection::GetSQLEncryption("street"));
    }
    
    public function down()
    {
    	$this->execute("UPDATE user_info SET first_name=".Connection::GetSQLDecryption("first_name"));
    	$this->execute("UPDATE user_info SET last_name=".Connection::GetSQLDecryption("last_name"));
    	$this->execute("UPDATE user_info SET street=".Connection::GetSQLDecryption("street"));
    	
    	$this->execute("ALTER TABLE user_info ALTER COLUMN first_name TYPE character varying(255)");
    	$this->execute("ALTER TABLE user_info ALTER COLUMN last_name TYPE character varying(255)");
    	$this->execute("ALTER TABLE user_info ALTER COLUMN street TYPE character varying(255)");
    }
}
