<?php


use Phinx\Migration\AbstractMigration;

class LanguageOcrErrors extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "ocr-fail", "Interne ocr-server-Fehler");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "ocr-fail", "Internal ocr server error");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "ocr-fail", "Interne ocr-server-Fehler");
        
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "ocr-timeout", "Ocr-server nicht erkennen kann, Eingang in der Zeit");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "ocr-timeout", "Ocr server can't recognize receipt in time");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "ocr-timeout", "Ocr-server nicht erkennen kann, Eingang in der Zeit");
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
