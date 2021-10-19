<?php

use Phinx\Migration\AbstractMigration;

class EmployeeDefaultSulatation extends AbstractMigration
{
    public function change()
    {
        //users and employees
        $stmt = GetStatement(DB_CONTROL);
        $userList = $this->fetchAll("SELECT user_id, salutation FROM user_info");
        foreach ($userList as $user)
        {
            if ($user["salutation"] === "" || $user["salutation"] == "HErr" || $user["salutation"] == "Herr" || $user["salutation"] == "Herr" ||
                $user["salutation"] == "Mr" || $user["salutation"] == "Mr." || $user["salutation"] == "mr")
            {
                $this->execute("UPDATE user_info SET salutation='mr' WHERE user_id=".Connection::GetSQLString($user["user_id"]));
                $query = "INSERT INTO user_history (end_user_id, property_name, value, created, start_user_id, created_from)
					VALUES (
					".$user["user_id"].",
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
                $this->execute("UPDATE user_info SET salutation='ms' WHERE user_id=".Connection::GetSQLString($user["user_id"]));
                $query = "INSERT INTO user_history (end_user_id, property_name, value, created, start_user_id, created_from)
					VALUES (
					".$user["user_id"].",
					'salutation',
					'ms',
					".Connection::GetSQLString(GetCurrentDateTime()).",
					'-2',
					'admin')
					RETURNING value_id";
                $stmt->Execute($query);
            }
        }

        //company unit contact
        $contactList = $this->fetchAll("SELECT contact_id, salutation FROM contact");
        foreach ($contactList as $contact)
        {
            if ($contact["salutation"] === "" || $contact["salutation"] == "HErr" || $contact["salutation"] == "Herr" || $contact["salutation"] == "Herr" ||
            $contact["salutation"] == "Mr" || $contact["salutation"] == "Mr." || $contact["salutation"] == "mr")
            {
                $this->execute("UPDATE contact SET salutation='mr' WHERE contact_id=".Connection::GetSQLString($contact["contact_id"]));
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
            else
            {
                $this->execute("UPDATE contact SET salutation='ms' WHERE contact_id=".Connection::GetSQLString($contact["contact_id"]));
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

        //partner contact
        $contactList = $this->fetchAll("SELECT partner_contact_id, salutation FROM partner_contact");
        foreach ($contactList as $contact)
        {
            if ($contact["salutation"] === "" || $contact["salutation"] == "HErr" || $contact["salutation"] == "Herr" || $contact["salutation"] == "Herr" ||
                $contact["salutation"] == "Mr" || $contact["salutation"] == "Mr." || $contact["salutation"] == "mr")
            {
                $this->execute("UPDATE partner_contact SET salutation='mr' WHERE partner_contact_id=".Connection::GetSQLString($contact["partner_contact_id"]));
                $query = "INSERT INTO partner_contact_history (contact_id, property_name, value, created, user_id)
					VALUES (
					".$contact["partner_contact_id"].",
					'salutation',
					'mr',
					".Connection::GetSQLString(GetCurrentDateTime()).",
					'-2')
					RETURNING value_id";
                $stmt->Execute($query);
            }
            else
            {
                $this->execute("UPDATE partner_contact SET salutation='ms' WHERE partner_contact_id=".Connection::GetSQLString($contact["partner_contact_id"]));
                $query = "INSERT INTO partner_contact_history (contact_id, property_name, value, created, user_id)
					VALUES (
					".$contact["partner_contact_id"].",
					'salutation',
					'ms',
					".Connection::GetSQLString(GetCurrentDateTime()).",
					'-2')
					RETURNING value_id";
                $stmt->Execute($query);
            }
        }
    }
}
