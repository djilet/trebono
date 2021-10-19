<?php


use Phinx\Migration\AbstractMigration;

class TravelManagementMultipleReceiptFiles extends AbstractMigration
{
    public function up()
    {
		$this->execute("UPDATE product_group SET multiple_receipt_file='Y' WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__TRAVEL));
    }
    
    public function down()
    {
		$this->execute("UPDATE product_group SET multiple_receipt_file='N' WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__TRAVEL));
    }
}
