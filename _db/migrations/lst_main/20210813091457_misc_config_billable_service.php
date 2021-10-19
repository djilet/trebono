<?php

use Phinx\Migration\AbstractMigration;

class MiscConfigBillableService extends AbstractMigration
{

    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-create_standard_billing_items", "Erstellen von Standard-Rechnungspositionen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-create_standard_billing_items", "Create standard Billing Items");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-create_standard_billing_items", "Erstellen von Standard-Rechnungspositionen");
    }
    
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('create_standard_billing_items', ' ','misc', 'plain', ".$updated.")");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='create_standard_billing_items'");
        
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}