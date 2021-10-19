<?php


use Phinx\Migration\AbstractMigration;

class TravelTripName extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "trip_name-empty", "Bitte geben Sie Reise, name");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "trip_name-empty", "Please, enter trip name");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "trip_name-empty", "Bitte geben Sie Reise, name");
    }
    
    public function up()
    {
        $this->table("trip")
        ->addColumn("trip_name", "string", array("length" => 255, "null" => true))
        ->save();
        
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
