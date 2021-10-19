<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class TripTableTravel extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "api-trip-finished-by-employee-success", "Trip finished");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "api-trip-finished-by-employee-success", "Reise beendet");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "api-trip-finished-by-employee-success", "Reise beendet");
        
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "api-error-trip-finished", "Trip finished");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "api-error-trip-finished", "Reise beendet");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "api-error-trip-finished", "Reise beendet");
        
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "Trip", "Trip ID:");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "Trip", "Reise ID");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "Trip", "Reise ID");
    }
    
    public function up()
    {
        $this->table("trip", ["id" => "trip_id"])
        ->addColumn("employee_id", "integer", ["null" => false])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("finished_by_employee", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
        
        $this->table("receipt")
        ->addColumn("trip_id", "integer", ["null" => true])
        ->save();
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->dropTable("trip");
        
        $this->table("receipt")
        ->removeColumn("trip_id")
        ->save();
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
