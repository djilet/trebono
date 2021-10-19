<?php


use Phinx\Migration\AbstractMigration;

class RemoveBenefitBothReceiptOptionValue extends AbstractMigration
{
    public function up()
	{
		$optionID = Option::GetOptionIDByCode(OPTION__BENEFIT__MAIN__RECEIPT_OPTION);
		
		$this->execute("UPDATE option_value_history SET value='yearly' WHERE value='both' AND option_id=".intval($optionID));	
	}
	
	public function down()
	{
		//cannot be rolled back
	}
}
