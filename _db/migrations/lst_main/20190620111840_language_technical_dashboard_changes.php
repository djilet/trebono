<?php


use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardChanges extends AbstractMigration
{
        private $langVarList = array();
        private $delLangVarList = array();
    
    public function init()
    {
        $this->delLangVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DateFrom", "ab Datum");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DateFrom", "Date from");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DateFrom", "ab Datum");
        $this->delLangVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DateTo", "bis Datum");
        $this->delLangVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DateTo", "Date to");
        $this->delLangVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DateTo", "bis Datum");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "DateRange", "Datumsbereich");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "DateRange", "Date range");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "DateRange", "Datumsbereich");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "TimeGroup", "Zeit Gruppe durch");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "TimeGroup", "Time group by");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "TimeGroup", "Zeit Gruppe durch");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "Minute", "Minute");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "Minute", "Minute");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "Minute", "Minute");      
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "Hour", "Stunde");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "Hour", "Hour");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "Hour", "Stunde");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "Second", "Zweite");
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "Second", "Second");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "Second", "Zweite");
        
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "limit-ocr-check-exceeded", "Suche ausgeführt wurde, nur für die ersten %limit% Punkte. Bitte, versuchen Sie, Ihre Suche einzugrenzen, um zu sehen, die vollständigen Ergebnisse.");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "limit-ocr-check-exceeded", "Search was run only for first %limit% points. Please, try to narrow your search to see full results.");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "limit-ocr-check-exceeded", "Suche ausgeführt wurde, nur für die ersten %limit% Punkte. Bitte, versuchen Sie, Ihre Suche einzugrenzen, um zu sehen, die vollständigen Ergebnisse.");
    
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
