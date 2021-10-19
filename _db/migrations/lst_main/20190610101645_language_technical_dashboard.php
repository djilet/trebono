<?php


use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboard extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_technical_dashboard.html", "TechnicalDashboard", "Dashboard");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "TechnicalDashboard", "Dashboard");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "TechnicalDashboard", "Dashboard");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DateFrom", "ab Datum");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DateFrom", "Date from");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DateFrom", "ab Datum");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DateTo", "bis Datum");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DateTo", "Date to");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DateTo", "bis Datum");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "ReceiptOCRRange", "Zeit Intervall");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "ReceiptOCRRange", "Time range");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "ReceiptOCRRange", "Zeit Bereich");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "ReceiptOCRStatistics", "Statistik: Eingang Anzahl Belege mit OCR  Erkennung");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "ReceiptOCRStatistics", "Receipt created with OCR recognize statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "ReceiptOCRStatistics", "Eingang erstellt, mit OCR erkennen-Statistik");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "QueueStatistics", "Queue-Statistik");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "QueueStatistics", "Queue statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "QueueStatistics", "Queue-Statistik");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "FilterApply", "Suche");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "FilterApply", "Search");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "FilterApply", "Suche");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-technical_dashboard", "Technische dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-technical_dashboard", "Technical dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-technical_dashboard", "Technische dashboard");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "ReceiptCreateCount", "Anzahl Belege concurrent übermittelt");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "ReceiptCreateCount", "Receipts created");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "ReceiptCreateCount", "Quittungen erstellt");
        
        $this->delLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptCreateCount", "Anzahl Belege concurrent übermittelt");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptCreateCount", "Receipts created");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptCreateCount", "Quittungen erstellt");
        $this->delLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptOCRRange", "Zeit Intervall");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptOCRRange", "Time range");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptOCRRange", "Zeit Bereich");
        $this->delLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Statistik: Eingang Anzahl Belege mit OCR  Erkennung");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Receipt created with OCR recognize statistics");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Eingang erstellt, mit OCR erkennen-Statistik");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-send_mail", "E-mail senden");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-send_mail", "Send mail");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-send_mail", "E-mail senden");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-line_recognize", "Linie erkennen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-line_recognize", "Line recognize");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-line_recognize", "Linie erkennen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-signature_create", "Signatur erstellen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-signature_create", "Signature create");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-signature_create", "Signatur erstellen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-signature_verify", "Signatur überprüfen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-signature_verify", "Signature verify");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-signature_verify", "Signatur überprüfen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-check_limits", "Erhalt zu prüfen Grenzen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-check_limits", "Receipt check limits");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-check_limits", "Erhalt zu prüfen Grenzen");
        
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "queue-error_handler", "Fehler melden");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "queue-error_handler", "Error log");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "queue-error_handler", "Fehler melden");   
    
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
