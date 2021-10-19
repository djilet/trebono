<?php


use Phinx\Migration\AbstractMigration;

class ConfigGivveTransactionMonthLimit extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-givve_transactions_month_limit", "Givve transaction month limit");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-givve_transactions_month_limit", "Givve Transaktionsmonatslimit");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-givve_transactions_month_limit", "Givve Transaktionsmonatslimit");
    }
    
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('givve_transactions_month_limit', '3','misc', 'field-float', ".$updated.")");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='givve_transactions_month_limit'");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
