<?php


use Phinx\Migration\AbstractMigration;

class FixPhpVars extends AbstractMigration
{  
    public function up()
    {
        $query = "UPDATE language_variable
                    SET template='common'
                    WHERE type='php'";
        $this->execute($query);
        
    }
    
    public function down()
    {
        
    }
}
