<?php


use Phinx\Migration\AbstractMigration;

class ChildCare extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO product_group (title, created, code, sort_order, receipts, multiple_receipt_file, need_check_image)
						VALUES (
							'Child Care Service',
							NOW(),
							".Connection::GetSQLString(PRODUCT_GROUP__CHILD_CARE).",
							'11',
							'Y',
							'Y',
							'N'
						)");
        
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__CHILD_CARE));
        
        //main product        
        $this->execute("INSERT INTO product (group_id, title, created, code, base_for_api)
						VALUES (
                            '".$group["group_id"]."',
							'Child Care',
							NOW(),
							".Connection::GetSQLString(PRODUCT__CHILD_CARE__MAIN).",
							'Y'
						)");
        
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__MAIN));
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MAX_MONTHLY).",
                            'Max. Value Per Month',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MONTHLY_DISCOUNT).",
                            'Discount for child care service',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'string',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__SALARY_OPTION).",
                            'Salary option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'N','Y','N'
						)");
        
        //advanced security        
        $this->execute("INSERT INTO product (group_id, title, created, code, base_for_api, inheritable)
						VALUES (
                            '".$group["group_id"]."',
							'Child Care',
							NOW(),
							".Connection::GetSQLString(PRODUCT__CHILD_CARE__ADVANCED_SECURITY).",
							'N',							
							'Y'
						)");
        
        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__ADVANCED_SECURITY));
        
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_DISCOUNT).",
                            'Discount for child care advanced security service',
                            '1',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
                            'currency',
                            ".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productAdvancedSecurity["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        
        //configs for default prices
        
        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";
        
        $values = array();
        
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE).",".Connection::GetSQLString(10).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE)))
           	$values[] = "(".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE)))
        	$values[] = "(".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
            
        $query.= implode(",", $values);
        
        if($values != array())
            $this->execute($query);
    }
    
    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__MAIN));
        $productAdvancedSecurity = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__ADVANCED_SECURITY));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."' OR product_id='".$productAdvancedSecurity["product_id"]."'");
        
        foreach($optionList as $option)
        {
            $this->execute("DELETE FROM option_value WHERE option_id='".$option["option_id"]."'");
        }
        
        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM option WHERE product_id='".$productAdvancedSecurity["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__MAIN));
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CHILD_CARE__ADVANCED_SECURITY));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__CHILD_CARE));
             
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__IMPLEMENTATION_PRICE);
        $this->execute($query);
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__MAIN__MONTHLY_PRICE);
        $this->execute($query);
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__IMPLEMENTATION_PRICE);
        $this->execute($query);
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__CHILD_CARE__ADVANCED_SECURITY__MONTHLY_PRICE);
        $this->execute($query);
    }
}
