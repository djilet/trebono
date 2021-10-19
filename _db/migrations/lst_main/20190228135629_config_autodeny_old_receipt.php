<?php


use Phinx\Migration\AbstractMigration;

class ConfigAutodenyOldReceipt extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_old_receipt', 'receipt old','r_autodeny', 'plain', ".$updated."),
                                            ('message_automatic_deny_old_receipt', 'Dear %first_name% %last_name% your receipt was denied','p_push', 'plain', ".$updated.")");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_old_receipt'");
        $this->execute("DELETE FROM config WHERE code='message_automatic_deny_old_receipt'");
    }
}
