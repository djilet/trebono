<?php


use Phinx\Migration\AbstractMigration;

class TravelManagmentMonthly extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE product_group SET need_check_image='N', sort_order=12
						WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__TRAVEL));

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__TRAVEL__MAIN));

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              'currency',
                              ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_PRICE).",
                              'Monthly service price',
                              '1',
                              '".$productMain["product_id"]."',
                              '1',
                           'N','Y','N'
                        )");
      $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                              'currency',
                              ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_DISCOUNT).",
                              'Discount for travel service',
                              '1',
                              '".$productMain["product_id"]."',
                              '1',
                           'N','Y','N'
                        )");

        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";

        $values = array();
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        $query.= implode(",", $values);

        if($values != array())
            $this->execute($query);
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_PRICE));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_DISCOUNT));

        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__TRAVEL__MAIN__MONTHLY_PRICE);
        $this->execute($query);
    }
}
