<?php

use Phinx\Migration\AbstractMigration;

class EmployeeDefaultSulatationFix extends AbstractMigration
{
    public function change()
    {
        //company unit contact
        $stmt = GetStatement(DB_CONTROL);
        $contactList = $this->fetchAll("SELECT contact_id, salutation FROM contact");
        foreach ($contactList as $contact)
        {
            if ($contact["salutation"] === "" || $contact["salutation"] == "HErr" || $contact["salutation"] == "Herr" || $contact["salutation"] == "Herr" ||
                $contact["salutation"] == "Mr" || $contact["salutation"] == "Mr." || $contact["salutation"] == "mr")
            {
                $query = "INSERT INTO contact_history (contact_id, property_name, value, created, user_id, created_from)
                    VALUES (
                    ".$contact["contact_id"].",
                    'salutation',
                    'mr',
					".Connection::GetSQLString(GetCurrentDateTime()).",
					'-2',
					'admin')
                    RETURNING value_id";
                $stmt->Execute($query);
            }
            else
            {
                $query = "INSERT INTO contact_history (contact_id, property_name, value, created, user_id, created_from)
                    VALUES (
                    ".$contact["contact_id"].",
                    'salutation',
                    'ms',
					".Connection::GetSQLString(GetCurrentDateTime()).",
					'-2',
					'admin')
                    RETURNING value_id";
                $stmt->Execute($query);
            }
        }
    }
}
