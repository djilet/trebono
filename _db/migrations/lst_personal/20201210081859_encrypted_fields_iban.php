<?php

use Phinx\Migration\AbstractMigration;

class EncryptedFieldsIban extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE EXTENSION IF NOT EXISTS pgcrypto");

        $this->execute("ALTER TABLE employee ALTER COLUMN iban TYPE text");

        $this->execute("UPDATE employee SET iban=".Connection::GetSQLEncryption("iban"));
    }

    public function down()
    {
        $this->execute("UPDATE employee SET iban=".Connection::GetSQLDecryption("iban"));

        $this->execute("ALTER TABLE employee ALTER COLUMN iban TYPE character varying(255)");
    }
}
