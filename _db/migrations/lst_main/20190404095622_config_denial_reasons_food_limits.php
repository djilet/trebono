<?php


use Phinx\Migration\AbstractMigration;

class ConfigDenialReasonsFoodLimits extends AbstractMigration
{
    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_day_limit", "Daily limit exceeded");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_day_limit", "Täglich Limit erreicht");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_day_limit", "Täglich Limit erreicht");
        
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-receipt_autodeny_week_limit", "Weekly limit exceeded");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-receipt_autodeny_week_limit", "Wöchentlich Limit erreicht");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-receipt_autodeny_week_limit", "Wöchentlich Limit erreicht");
    }
    
    
    
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_day_limit', 'das tägliche Limit bereits erreicht wurde','r_autodeny', 'plain', ".$updated.")");
        
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_week_limit', 'das wöchentliche Limit bereits erreicht wurde','r_autodeny', 'plain', ".$updated.")");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_day_limit'");
        $this->execute("DELETE FROM config WHERE code='receipt_autodeny_week_limit'");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
