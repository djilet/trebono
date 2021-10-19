<?php


use Phinx\Migration\AbstractMigration;

class OptionFoodWeeklyShoppingReceiptPeriod extends AbstractMigration
{
    public function up()
    {
		$this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES 
						(nextval('\"option_key_KeyID_seq\"'::regclass),
						'int',
						".Connection::GetSQLString(OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD).",
						'Weekly purchase receipt period (days)',
						'5',
						".intval(Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)).",
						'3',
						'Y','Y','Y');");
    }
    
    public function down()
    {
    	$this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD));
    }
}
