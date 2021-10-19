<?php


use Phinx\Migration\AbstractMigration;

class ProductGroupBonus extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Bonus Service',NOW(),
							'bonus',
							'9',
							'N'
						)");
        
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code='bonus'");
        
        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Bonus',NOW(),
							'bonus__main',
							'Y'
						)");
        
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='bonus__main'");
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'bonus__main__max_yearly',
                            'Max. Yearly Value',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'bonus__main__monthly_price',
                            'Monthly service price',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'bonus__main__monthly_discount',
                            'Discount for bonus service',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'bonus__main__implementation_price',
                            'Implementation fee',
                            '3',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'bonus__main__implementation_discount',
                            'Discount for implem. fee',
                            '4',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            'bonus__main__salary_option',
                            'Salary option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'N','Y','Y'
						)");
    }
    
    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='bonus__main'");
        
        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code='bonus__main'");
        $this->execute("DELETE FROM product_group WHERE code='bonus'");
    }
}
