<?php


use Phinx\Migration\AbstractMigration;

class LanguagePermissionService extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "commonl", "permission-service", "Service administrator");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-service", "Dienstadministrator");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-service", "Dienstadministrator");
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
