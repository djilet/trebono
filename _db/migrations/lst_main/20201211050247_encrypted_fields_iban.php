<?php

use Phinx\Migration\AbstractMigration;

class EncryptedFieldsIban extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE EXTENSION IF NOT EXISTS pgcrypto");

        $this->execute("ALTER TABLE company_unit ALTER COLUMN iban TYPE text");
        $this->execute("ALTER TABLE partner ALTER COLUMN iban TYPE text");

        $this->execute("UPDATE company_unit SET iban=".Connection::GetSQLEncryption("iban"));
        $this->execute("UPDATE partner SET iban=".Connection::GetSQLEncryption("iban"));
    }

    public function down()
    {
        $this->execute("UPDATE company_unit SET iban=".Connection::GetSQLDecryption("iban"));
        $this->execute("UPDATE partner SET iban=".Connection::GetSQLDecryption("iban"));

        $this->execute("ALTER TABLE company_unit ALTER COLUMN iban TYPE character varying(255)");
        $this->execute("ALTER TABLE partner ALTER COLUMN iban TYPE character varying(255)");
    }
}
