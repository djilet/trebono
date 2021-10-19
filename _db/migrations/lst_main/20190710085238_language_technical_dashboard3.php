<?php


use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboard3 extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();
    
    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRAllCount", "C. Alle OCR Anfragen (inkl. fehlerhaften Anfragen)");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRAllCount", "All requests");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRAllCount", "Alle Anfragen");
   
        $this->delLangVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "B. Erfolgreiche OCR Anfragen (mit und ohne Beleg)");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "Successful requests");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "Erfolgreiche Anfragen");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRNoReceiptCount", "Anfragen, die nicht mit einer gültigen Quittung auf dem Bild");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRNoReceiptCount", "Requests with not a valid receipt in the picture");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRNoReceiptCount", "Anfragen, die nicht mit einer gültigen Quittung auf dem Bild");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRUnsuccessfulCount", "Fehlgeschlagene Anfragen");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRUnsuccessfulCount", "Failed requests");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRUnsuccessfulCount", "Fehlgeschlagene Anfragen");
        
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
