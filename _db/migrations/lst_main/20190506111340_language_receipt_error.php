<?php


use Phinx\Migration\AbstractMigration;

class LanguageReceiptError extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-restaurant-weekend", "Eingang vom restaurant kann nicht genehmigt werden, an den Wochenenden");
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-restaurant-weekend", "Receipt from restaurant can't be approved on weekends");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-restaurant-weekend", "Eingang vom restaurant kann nicht genehmigt werden, an den Wochenenden");
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
