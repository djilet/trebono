<?php

use Phinx\Migration\AbstractMigration;

class VoucherServicesNewOptionGlobalValue extends AbstractMigration
{
    public function up()
    {
        $stmt = GetStatement();
        $option = $stmt->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION));

        $this->execute("INSERT INTO option_value_history (value_id,level,entity_id,option_id,value,created,user_id,created_from)
						VALUES (
							nextval('\"option_value_history_ValueID_seq\"'::regclass),
                            'global',
                            '0',
                            '".$option["option_id"]."',
                            'Y',
                            ".Connection::GetSQLString(GetCurrentDateTime()).",
                            '-1',
							'admin'
						)");

        $option = $stmt->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION));

        $this->execute("INSERT INTO option_value_history (value_id,level,entity_id,option_id,value,created,user_id,created_from)
						VALUES (
							nextval('\"option_value_history_ValueID_seq\"'::regclass),
                            'global',
                            '0',
                            '".$option["option_id"]."',
                            'Y',
                            ".Connection::GetSQLString(GetCurrentDateTime()).",
                            '-1',
							'admin'
						)");

    }

    public function down()
    {
        $stmt = GetStatement();

        $option = $stmt->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".$option["option_id"]);

        $option = $stmt->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION));
        $this->execute("DELETE FROM option_value_history WHERE option_id=".$option["option_id"]);
    }
}
