<?php


use Phinx\Migration\AbstractMigration;

class ReceiptAddFields extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "RefNumber", "Belegnummer Buchhaltungssystem");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "RefNumber", "Reference Number Bookkeeping System");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "RefNumber", "Belegnummer Buchhaltungssystem");
        
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "AccSystem", "Buchungskonto Buchhaltungssystem");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "AccSystem", "Account Bookkeeping System");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "AccSystem", "Buchungskonto Buchhaltungssystem");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        $this->table("receipt")
        ->addColumn("ref_number", "string", ["length" => 255, "null" => true])
        ->addColumn("acc_system", "string", ["length" => 255, "null" => true])
        ->save();
    }
    
    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
        
        $this->table("receipt")
        ->removeColumn("ref_number")
        ->removeColumn("acc_system")
        ->save();
    }
}
