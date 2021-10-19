<?php

use Phinx\Migration\AbstractMigration;

class StoredDataFix2 extends AbstractMigration
{
    public function up()
    {
        //clear history
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));
        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id=".$option["option_id"];
        $stmt->Execute($query);

        //delete option services
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));
    }

    public function down()
    {
        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                            'Services',
                            '1',
                            '".$product["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

    }
}
