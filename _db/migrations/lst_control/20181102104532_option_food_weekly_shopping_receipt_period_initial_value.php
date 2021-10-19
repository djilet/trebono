<?php


use Phinx\Migration\AbstractMigration;

class OptionFoodWeeklyShoppingReceiptPeriodInitialValue extends AbstractMigration
{
	public function up()
	{
		$optionID = Option::GetOptionIDByCode(OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD);
		
		$this->execute("INSERT INTO option_value_history (level, entity_id, option_id, value, created, user_id)
						VALUES 
						
						(
						".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
						0, 
						".intval($optionID).",
						'7',
						'2018-01-01 00:00:00',
						1);");
	}
	
	public function down()
	{
		$optionID = Option::GetOptionIDByCode(OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD);
		
		$this->execute("DELETE FROM option_value_history 
						WHERE option_id=".intval($optionID)." 
							AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." 
							AND created='2018-01-01 00:00:00' 
							AND user_id=1");
	}
}
