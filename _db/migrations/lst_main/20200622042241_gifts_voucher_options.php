<?php

use Phinx\Migration\AbstractMigration;

class GiftsVoucherOptions extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT, "Payroll export");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT, "Export in die Gehaltsabrechnung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT, "Export in die Gehaltsabrechnung");
    }

    public function up()
    {
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT).",
                            'Payroll export',
                            '3',
                            '".Product::GetProductIDByCode(PRODUCT__GIFT_VOUCHER__MAIN)."',
                            '3',
							'Y','Y','Y'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $this->execute("UPDATE option SET level_global='N' WHERE code IN ('".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
        $this->execute("UPDATE option SET level_global='N' WHERE code IN ('".OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code='".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT)."'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $this->execute("UPDATE option SET level_global='Y' WHERE code IN ('".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
        $this->execute("UPDATE option SET level_global='Y' WHERE code IN ('".OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_DISCOUNT."', '".OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT."')");
    }
}
