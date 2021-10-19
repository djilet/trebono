<?php


use Phinx\Migration\AbstractMigration;

class EmployeeLastMobileApplicationClientAndVersion extends AbstractMigration
{
    public function up()
    {
    	$this->table("employee")
			->addColumn("last_mobile_application_client", "string", ["length" => 10, "null" => true])
			->addColumn("last_mobile_application_version", "string", ["length" => 255, "null" => true])
			->save();
    	
		$this->execute("UPDATE employee AS e 
			SET last_mobile_application_client=sq.client
			FROM (
				SELECT MAX(client) AS client, user_id 
				FROM device 
				GROUP BY user_id 
			) AS sq 
			WHERE e.user_id=sq.user_id");
    }
    
    public function down()
    {
    	$this->table("employee")
    		->removeColumn("last_mobile_application_client")
    		->removeColumn("last_mobile_application_version")
    		->save();
    }
}
