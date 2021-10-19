<?php


use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboard2 extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRAllCount", "Alle Anfragen");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRAllCount", "All requests");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRAllCount", "Alle Anfragen");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "Erfolgreiche Anfragen");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "Successful requests");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRSuccessfulCount", "Erfolgreiche Anfragen");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRReceiptCount", "Eingang enthalten Anfragen");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRReceiptCount", "Receipt contain requests");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRReceiptCount", "Eingang enthalten Anfragen");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "OCRStatistics", "OCR-Anforderungen Statistik");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "OCRStatistics", "OCR requests statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "OCRStatistics", "OCR-Anforderungen Statistik");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
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
    }
}
