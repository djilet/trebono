<?php


use Phinx\Migration\AbstractMigration;

class LanguageDashboardOcr extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Eingang erstellt, mit OCR erkennen-Statistik");
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Receipt created with OCR recognize statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptOCRStatistics", "Eingang erstellt, mit OCR erkennen-Statistik");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptOCRRange", "Zeit Bereich");
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptOCRRange", "Time range");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptOCRRange", "Zeit Bereich");
        
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "ReceiptCreateCount", "Quittungen erstellt");
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "ReceiptCreateCount", "Receipts created");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "ReceiptCreateCount", "Quittungen erstellt");
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
