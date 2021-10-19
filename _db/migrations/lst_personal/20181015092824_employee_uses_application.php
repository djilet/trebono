<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class EmployeeUsesApplication extends AbstractMigration
{
    public function up()
    {
    	$this->table("employee")
			->addColumn("uses_application", Literal::from("flag"), ["default" => "N"])
			->save();
    	
		$this->execute("UPDATE employee AS e 
			SET uses_application='Y' 
			FROM (
				SELECT user_id 
				FROM device 
				GROUP BY user_id 
			) AS sq 
			WHERE e.user_id=sq.user_id");
    }
    
    public function down()
    {
    	$this->table("employee")
    		->removeColumn("uses_application")
    		->save();
    }
}
