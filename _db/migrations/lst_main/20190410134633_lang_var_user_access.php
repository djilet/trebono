<?php


use Phinx\Migration\AbstractMigration;

class LangVarUserAccess extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "access-token-expire-or-wrong", "Access token expire or not exist");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "access-token-expire-or-wrong", "Das Zugriffstoken läuft ab oder ist nicht vorhanden");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "access-token-expire-or-wrong", "Das Zugriffstoken läuft ab oder ist nicht vorhanden");
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
