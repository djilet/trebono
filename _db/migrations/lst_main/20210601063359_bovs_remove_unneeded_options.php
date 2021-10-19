<?php

use Phinx\Migration\AbstractMigration;

class BovsRemoveUnneededOptions extends AbstractMigration
{
    public function up()
    {
        $this->execute("DELETE FROM option WHERE code='OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_FIXED'");
        $this->execute("DELETE FROM option WHERE code='OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_ACTIVE'");
    }

    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BONUS_VOUCHER__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'bonus_voucher__main__default_reason_active',
                            'Voucher preference fixed',
                            '1',
                            '".$productMain["product_id"]."',
                            '4',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            'bonus_voucher__main__default_reason_fixed',
                            'Voucher preference fixed',
                            '3',
                            '".$productMain["product_id"]."',
                            '4',
							'Y','Y','Y'
						)");
    }
}
