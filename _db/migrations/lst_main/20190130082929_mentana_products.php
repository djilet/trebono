<?php


use Phinx\Migration\AbstractMigration;

class MentanaProducts extends AbstractMigration
{
    public function up()
    {
        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        foreach($productGroupList->GetItems() as $productGroup)
        {
            if($productGroup["receipts"] != "N")
            {
                if(!$this->fetchRow("SELECT code FROM product WHERE code='".$productGroup["code"]."__advanced_security'"))
                {
                    $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
    						VALUES (
    							nextval('\"product_ProductID_seq\"'::regclass),
                                '".$productGroup["group_id"]."',
    							'".$productGroup["title_translation"]." Advanced Security',NOW(),
    							'".$productGroup["code"]."__advanced_security',
    							'N'
    						)");
                }
                
                if($product = $this->fetchRow("SELECT * FROM product WHERE code='".$productGroup["code"]."__advanced_security'"))
                {
                $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'currency',
                        '".$productGroup["code"]."__advanced_security__monthly_price',
                        'Monthly service price',
                        '1',
                        '".$product["product_id"]."',
                        '1',
						'N','Y','N'
					) ON CONFLICT (code) DO NOTHING");
                $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'currency',
                        '".$productGroup["code"]."__advanced_security__monthly_discount',
                        'Discount for ".$productGroup["code"]." advanced security service',
                        '1',
                        '".$product["product_id"]."',
                        '1',
						'N','Y','N'
					) ON CONFLICT (code) DO NOTHING");
                $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'currency',
                        '".$productGroup["code"]."__advanced_security__implementation_price',
                        'Implementation fee',
                        '3',
                        '".$product["product_id"]."',
                        '1',
						'N','Y','N'
					) ON CONFLICT (code) DO NOTHING");
                $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'currency',
                        '".$productGroup["code"]."__advanced_security__implementation_discount',
                        'Discount for implem. fee',
                        '4',
                        '".$product["product_id"]."',
                        '1',
						'N','Y','N'
					) ON CONFLICT (code) DO NOTHING");

                $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";
                
                $values = array();
                
                if(!$this->fetchRow("SELECT code FROM config WHERE code='".$productGroup["code"]."__advanced_security__implementation_price'"))
                    $values[] = "('".$productGroup["code"]."__advanced_security__implementation_price',".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
                if(!$this->fetchRow("SELECT code FROM config WHERE code='".$productGroup["code"]."__advanced_security__monthly_price'"))
                    $values[] = "('".$productGroup["code"]."__advanced_security__monthly_price',".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
                
                $query.= implode(",", $values);
                if($values != array())
                    $this->execute($query);
                }
            }
        }
    }
    
    public function down()
    {
        $this->execute("DELETE FROM product WHERE code LIKE '%__advanced_security'");
        $this->execute("DELETE FROM option WHERE code LIKE '%__advanced_security__%'");
        $this->execute("DELETE FROM config WHERE code LIKE '%__advanced_security__%'");
    }
}
