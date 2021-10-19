<?php


use Phinx\Migration\AbstractMigration;

class LanguaageFixCompanyReplacements extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company-house", "Hausnr. Unternehmen");
        
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "replacement-company_city", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "company", "common", "replacement-company_house", "Hausnr. Unternehmen");
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