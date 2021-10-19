<?php


use Phinx\Migration\AbstractMigration;

class MessageAndReasonAutodenyIntegrityCheck extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_integrity_check', 'Integrity check failed','r_autodeny', 'plain', ".$updated.")");
        
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                    VALUES('message_automatic_denyal_integrity_check', 'Hallo %salutation% %first_name% %last_name%, Die Integritätsprüfung der Empfangsbestätigung ist fehlgeschlagen. Vielen Dank, Ihr trebono Team.',
                           'p_push', 'plain', '".GetCurrentDateTime()."')");
    }
    
    public function down(){
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_integrity_check'");
        $this->execute("DELETE FROM config WHERE code='message_automatic_denyal_integrity_check'");
    }
}
