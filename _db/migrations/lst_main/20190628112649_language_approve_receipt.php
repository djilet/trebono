<?php


use Phinx\Migration\AbstractMigration;

class LanguageApproveReceipt extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ApproveReceiptByEmployee", "Genehmigen durch Mitarbeiter");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ApproveReceiptByEmployee", "Approve by employee");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ApproveReceiptByEmployee", "Genehmigen durch Mitarbeiter");
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
