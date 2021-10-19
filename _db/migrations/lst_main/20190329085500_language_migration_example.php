<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationExample extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Value");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Value");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TypeHospitality", "Value");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $query = $langVar->GetUpdateQuery();
            //$this->execute($query);
        }
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            //$this->execute($query);
        }
    }
}
