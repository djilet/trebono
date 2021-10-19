<?php


use Phinx\Migration\AbstractMigration;

class ConfigPushRemindReceiptExpire extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_receipt_expire_5", "Text of notification when receipt expires in 5 days (services with number of payment month option)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_receipt_expire_5", "Benachrichtigungstext, wenn der Eingang in 5 Tagen abläuft (Dienstleistungen mit der Option Anzahl der Zahlungsmonate)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_receipt_expire_5", "Benachrichtigungstext, wenn der Eingang in 5 Tagen abläuft (Dienstleistungen mit der Option Anzahl der Zahlungsmonate)");
        
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_receipt_expire_15", "Text of notification when receipt expires in 15 days (services with number of payment month option)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_receipt_expire_15", "Benachrichtigungstext, wenn der Eingang in 15 Tagen abläuft (Dienstleistungen mit der Option Anzahl der Zahlungsmonate)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_receipt_expire_15", "Benachrichtigungstext, wenn der Eingang in 15 Tagen abläuft (Dienstleistungen mit der Option Anzahl der Zahlungsmonate)");
    }
    
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('push_remind_receipt_expire_5', 'Dear %salutation% %first_name% %last_name% your receipt for %service% will be expire in %days_left% days','p_push', 'plain', ".$updated."),
                                            ('push_remind_receipt_expire_15', 'Dear %salutation% %first_name% %last_name% your receipt for %service% will be expire in %days_left% days','p_push', 'plain', ".$updated.")");
    
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='push_remind_receipt_expire_5'");
        $this->execute("DELETE FROM config WHERE code='push_remind_receipt_expire_15'");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
