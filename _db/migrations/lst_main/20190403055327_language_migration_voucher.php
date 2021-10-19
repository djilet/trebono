<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationVoucher extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "new-voucher", "New voucher");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "new-voucher", "Neuer Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "new-voucher", "Neuer Gutschein");
    }
    
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            //$query = $langVar->GetUpdateQuery();
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
