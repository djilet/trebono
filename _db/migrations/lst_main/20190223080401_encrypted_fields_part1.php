<?php


use Phinx\Migration\AbstractMigration;

class EncryptedFieldsPart1 extends AbstractMigration
{
    public function up()
	{
		$this->execute("CREATE EXTENSION IF NOT EXISTS pgcrypto");
		
		$this->execute("ALTER TABLE company_unit ALTER COLUMN title TYPE text");
		$this->execute("ALTER TABLE company_unit ALTER COLUMN street TYPE text");
		
		$this->execute("UPDATE company_unit SET title=".Connection::GetSQLEncryption("title"));
		$this->execute("UPDATE company_unit SET street=".Connection::GetSQLEncryption("street"));
	}
	
	public function down()
	{
		$this->execute("UPDATE company_unit SET title=".Connection::GetSQLDecryption("title"));
		$this->execute("UPDATE company_unit SET street=".Connection::GetSQLDecryption("street"));
		
		$this->execute("ALTER TABLE company_unit ALTER COLUMN title TYPE character varying(255)");
		$this->execute("ALTER TABLE company_unit ALTER COLUMN street TYPE character varying(255)");
	}
}
