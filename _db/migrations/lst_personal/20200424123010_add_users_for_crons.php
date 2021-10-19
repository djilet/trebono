<?php

use Phinx\Migration\AbstractMigration;

class AddUsersForCrons extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO user_info (user_id, email, password, salutation, first_name, last_name, created, archive) 
                            VALUES ('-4','fineasy.servicerg@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Service RG'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-5','fineasy.gutscheinrg@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Gutschein RG'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-6','fineasy.datensicherung@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Datensicherung'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-7','fineasy.sbutscheine@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'SB Gutscheine'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-8','fineasy.essengutscheine@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Essen Gutscheine'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-9','fineasy.dauergutscheine@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Dauergutscheine'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-10','fineasy.ocr@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'OCR'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y'),
                                ('-11','fineasy.lohn@2kscs.de','','Frau',
                                ".Connection::GetSQLEncryption("'trebono'").",
                                ".Connection::GetSQLEncryption("'Lohn'").",
                                ".Connection::GetSQLString(GetCurrentDateTime()).",	'Y')
                            ");
    }

    public function down()
    {
        $this->execute("DELETE FROM user_info WHERE user_id in ('-4','-5','-6','-7','-8','-9','-10','-11')");
    }
}
