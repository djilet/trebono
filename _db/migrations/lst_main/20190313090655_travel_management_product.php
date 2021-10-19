<?php


use Phinx\Migration\AbstractMigration;

class TravelManagementProduct extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Travel Management Service',NOW(),
							'travel',
							'10',
							'Y'
						)");
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code='travel'");

        //main product
        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Travel Management',NOW(),
							'travel__main',
							'Y'
						)");
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='travel__main'");

        //implementation and implementation discount
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            'travel__main__implementation_price',
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
                            'travel__main__implementation_discount',
                            'Discount for implem. fee',
                            '4',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");

        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";

        $values = array();

        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__IMPLEMENTATION_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__TRAVEL__MAIN__IMPLEMENTATION_PRICE).",".Connection::GetSQLString(10).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";

        $query.= implode(",", $values);

        if($values != array())
            $this->execute($query);
    }

    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code='travel__main'");
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."'");

        foreach($optionList as $option)
        {
            $this->execute("DELETE FROM option_value WHERE option_id='".$option["option_id"]."'");
        }

        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code='travel__main'");
        $this->execute("DELETE FROM product_group WHERE code='travel'");

        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__IMPLEMENTATION_PRICE);

        $this->execute($query);
    }
}
