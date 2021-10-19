<?php


use Phinx\Migration\AbstractMigration;

class MakeRecreationWithoutOcr extends AbstractMigration
{
    public function up()
    {
    	$this->execute("UPDATE product_group SET need_check_image='N' WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__RECREATION));
    }
    
    public function down()
    {
    	$this->execute("UPDATE product_group SET need_check_image='Y' WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__RECREATION));
    }
}
