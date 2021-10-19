<?php


use Phinx\Migration\AbstractMigration;

class LanguageSavedMessage extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "saved", "Gespeichert");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "saved", "Saved");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "saved", "Gespeichert");
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
