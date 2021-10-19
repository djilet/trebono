<?php

require_once(dirname(__FILE__) . "/../include/init.php");

/*UPDATE  SET email = concat(left(email, strpos(email, '@') - 1),
floor(random() * 999 + 1)::int,
'@2kscs.de')
WHERE user_id > 1

UPDATE  SET iban = concat('DE', floor(random() * 999999 + 1)::int)

DELETE FROM _history WHERE property_name = 'email' OR property_name = 'iban'
*/

$stmt = GetStatement(DB_PERSONAL);

var_dump($stmt->Execute("UPDATE user_info SET email = concat(left(email, strpos(email, '@') - 1), 
    floor(random() * 999 + 1)::int, 
    '@2kscs.de')
WHERE user_id > 1"));

var_dump($stmt->Execute("UPDATE contact SET email = concat(left(email, strpos(email, '@') - 1), 
    floor(random() * 999 + 1)::int,
    '@2kscs.de')"));

var_dump($stmt->Execute("UPDATE partner_contact SET email = concat(left(email, strpos(email, '@') - 1), 
    floor(random() * 999 + 1)::int,
    '@2kscs.de')"));

var_dump($stmt->Execute("UPDATE employee SET 
    iban = concat('DE', floor(random() * (999999-100000+1) + 100000)::int)"));

var_dump($stmt->Execute("UPDATE device SET 
    push_token = NULL"));

$stmt = GetStatement();

var_dump($stmt->Execute("UPDATE partner SET email = concat(left(email, strpos(email, '@') - 1), 
    floor(random() * 999 + 1)::int,
    '@2kscs.de'),
    iban = concat('DE', floor(random() * (999999-100000+1) + 100000)::int)"));

var_dump($stmt->Execute("UPDATE company_unit SET email = concat(left(email, strpos(email, '@') - 1), 
    floor(random() * 999 + 1)::int,
    '@2kscs.de'),
    iban = concat('DE', floor(random() * (999999-100000+1) + 100000)::int)"));

$stmt = GetStatement(DB_CONTROL);

var_dump($stmt->Execute("DELETE FROM user_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));

var_dump($stmt->Execute("DELETE FROM contact_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));

var_dump($stmt->Execute("DELETE FROM partner_contact_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));

var_dump($stmt->Execute("DELETE FROM employee_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));

var_dump($stmt->Execute("DELETE FROM partner_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));

var_dump($stmt->Execute("DELETE FROM partner_contact_history 
    WHERE property_name = 'email' OR property_name = 'iban'"));
