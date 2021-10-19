<?php


use Phinx\Migration\AbstractMigration;

class InsertConfigOrgGuidelineHistory extends AbstractMigration
{
    public function up()
    {
         $config = new Config();
         $config->LoadByID(Config::GetIDByCode("app_org_guideline"));
         
         $query = "INSERT INTO config_history (user_id, config_id, value, created) VALUES (
         1, " .
         $config->GetIntProperty("config_id") . ", " .
         $config->GetPropertyForSQL("value") . ", " .
         $config->GetPropertyForSQL("updated") . ")";
         $this->execute($query);
    }
    
    public function down(){
        
        $query = "DELETE FROM config_history WHERE config_id=".intval(Config::GetIDByCode("app_org_guideline"));
         $this->execute($query);
    }
}
