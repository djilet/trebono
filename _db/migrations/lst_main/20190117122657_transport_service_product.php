<?php


use Phinx\Migration\AbstractMigration;

class TransportServiceProduct extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Public Transportation Service',NOW(),
							'transport',
							'10',
							'Y'
						)");
        
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code='transport'");
        
        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Public Transportation',NOW(),
							'transport__main',
							'Y'
						)");
        
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='transport__main'");
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'transport__main__max_monthly',
                            'Max. Value Per Month',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'transport__main__monthly_price',
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
                            'transport__main__monthly_discount',
                            'Discount for public transportation service',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'transport__main__implementation_price',
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
                            'transport__main__implementation_discount',
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
                            'transport__main__salary_option',
                            'Salary option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'N','Y','N'
						)");
        
        
        $this->table("company_unit")
        ->addColumn("acc_transport_tax_free", "string", ["length" => 255, "null" => true])
        ->save();     
        
        
        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";
        
        $values = array();
        
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__IMPLEMENTATION_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__IMPLEMENTATION_PRICE).",".Connection::GetSQLString(10).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__MONTHLY_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__MONTHLY_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
            
        $query.= implode(",", $values);
        
        if($values != array())
            $this->execute($query);
    }
    
    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='transport__main'");
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."'");
        
        foreach($optionList as $option)
        {
            $this->execute("DELETE FROM option_value WHERE option_id='".$option["option_id"]."'");
        }
        
        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code='transport__main'");
        $this->execute("DELETE FROM product_group WHERE code='transport'");
        
        $this->table("company_unit")
        ->removeColumn("acc_transport_tax_free")
        ->save();
        
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__IMPLEMENTATION_PRICE);
        
        $this->execute($query);
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__TRANSPORT__MAIN__MONTHLY_PRICE);
        
        $this->execute($query);
    }
}
