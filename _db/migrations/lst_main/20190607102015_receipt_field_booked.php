<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ReceiptFieldBooked extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "Booked", "Gebucht");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "Booked", "Booked");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "Booked", "Gebucht");
        
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-booked-error", "Gebucht-Eigenschaft kann nicht festgelegt werden 'Y' für nicht genehmigte Zugänge");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-booked-error", "Booked property cannot be set to 'Y' for non-approved receipts");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-booked-error", "Gebucht-Eigenschaft kann nicht festgelegt werden 'Y' für nicht genehmigte Zugänge");
    
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterNotBooked", "Nicht gebucht");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterNotBooked", "Not booked");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterNotBooked", "Nicht gebucht");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        
        $this->table("receipt")
        ->addColumn("booked", Literal::from("flag"), ["null" => true])
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
        ->removeColumn("booked")
        ->save();
    }
}
