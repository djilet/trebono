<?php
require_once(dirname(__FILE__)."/../../../include/init.php");

use Phinx\Migration\AbstractMigration;

class ForceApprovalOption extends AbstractMigration
{
    public function up()
    {
        $baseMainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'flag',
                        'base__force_approval',
                        'Receipts force approval',
                        '1',
                        '".$baseMainProductID."',
                        '3',
						'N','Y','Y'
					)");
    }
    
    public function down()
    {
        $this->execute("DELETE FROM option WHERE code='base__force_approval'");
    }
}
