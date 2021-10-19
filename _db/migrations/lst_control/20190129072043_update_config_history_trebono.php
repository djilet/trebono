<?php


use Phinx\Migration\AbstractMigration;

class UpdateConfigHistoryTrebono extends AbstractMigration
{
    public function up()
    {
        $configList = $this->fetchAll("SELECT DISTINCT ON(config_id) * FROM config_history WHERE value LIKE '%FIN-easy%' OR value LIKE '%2kscs.de%' OR value LIKE '%2ks-cs.de%' OR value like '2KS-CS.de' OR value like 'FINEasy' OR value like 'FINEASY' ORDER BY config_id, created");

        foreach($configList as $config)
        {
            $value = $config["value"];
            $value = str_replace("FIN-easy", "trebono", $value);
            $value = str_replace("FINEasy", "trebono", $value);
            $value = str_replace("FINEASY", "TREBONO", $value);
            $value = str_replace("2kscs.de", "trebono.de", $value);
            $value = str_replace("2ks-cs.de", "trebono.de", $value);
            $value = str_replace("2KS-CS.de", "trebono.de", $value);
            
            $this->execute("INSERT INTO config_history (user_id, config_id, value, created)
                                        VALUES (".SERVICE_USER_ID.", ".$config["config_id"].", '".$value."', NOW())");
        }
    }
    
    public function down()
    {
        $configList = $this->fetchAll("SELECT DISTINCT ON(config_id) * FROM config_history WHERE value LIKE '%trebono%' OR value LIKE '%trebono.de%' OR value LIKE '%FINEASY%' ORDER BY config_id, created");
        
        foreach($configList as $config)
        {
            $value = $config["value"];
            $value = str_replace("trebono.de", "2kscs.de", $value);
            $value = str_replace("trebono", "FIN-easy", $value);
            $value = str_replace("TREBONO", "FINEASY", $value);
            
            $this->execute("INSERT INTO config_history (user_id, config_id, value, created)
                                        VALUES (".SERVICE_USER_ID.", ".$config["config_id"].", '".$value."', NOW())");
        }
    }
}
