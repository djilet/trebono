<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ImportAsynch extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "Updated", "Aktualisiert");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "Updated", "Updated");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "Updated", "Aktualisiert");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "CompanyUnit", "Unternehmen Einheit");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "CompanyUnit", "Company unit");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "CompanyUnit", "Unternehmen Einheit");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "EmployeeCount", "Erstellt Mitarbeitern zählen");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "EmployeeCount", "Created employees count");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "EmployeeCount", "Erstellt Mitarbeitern zählen");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "Completed", "Import abgeschlossen");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "Completed", "Import completed");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "Completed", "Import abgeschlossen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "section-import", "Unternehmen Gerät importieren");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "section-import", "Company unit import");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "section-import", "Unternehmen Gerät importieren");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "QueueCount", "Anzahl Nachrichten in der Warteschlange:");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "QueueCount", "Count messages in queue:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "QueueCount", "Anzahl Nachrichten in der Warteschlange:");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "SendedLastHour", "Verschickte Nachrichten der letzten Stunde:");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "SendedLastHour", "Sended messages last hour:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "SendedLastHour", "Verschickte Nachrichten der letzten Stunde:");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "DoesStopped", "Warteschlange ist beendet:");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "DoesStopped", "Queue does stopped:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "DoesStopped", "Warteschlange ist beendet:");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "Limit", "Limit Nachrichten pro Stunde:");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "Limit", "Limit messages per hour:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "Limit", "Limit Nachrichten pro Stunde:");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-send_mail_hour_limit", "E-mail senden-Stunden-Grenze");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-send_mail_hour_limit", "Send mail hour limit");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-send_mail_hour_limit", "E-mail senden-Stunden-Grenze");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-send_mail_stop", "E-mail senden stop");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-send_mail_stop", "Send mail stop");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-send_mail_stop", "E-mail senden stop");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('send_mail_hour_limit', '150','misc', 'field-float', ".$updated."),
                                            ('send_mail_stop', 'N','misc', 'flag', ".$updated.")");
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        $this->execute("DELETE FROM config WHERE code='send_mail_hour_limit'");
        $this->execute("DELETE FROM config WHERE code='send_mail_stop'");
    }
}
