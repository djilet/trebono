<?php

use Phinx\Migration\AbstractMigration;

class FoodVoucherService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-group-".PRODUCT_GROUP__FOOD_VOUCHER, "Food Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-group-".PRODUCT_GROUP__FOOD_VOUCHER, "Essensmarken Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-group-".PRODUCT_GROUP__FOOD_VOUCHER, "Essensmarken Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-".PRODUCT__FOOD_VOUCHER__MAIN, "Food Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-".PRODUCT__FOOD_VOUCHER__MAIN, "Essensmarken Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-".PRODUCT__FOOD_VOUCHER__MAIN, "Essensmarken Gutschein");


        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE, "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Discount for benefit voucher service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION, "Salary option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION, "Gehaltsoption");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION, "Gehaltsoption");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK, "Units per week");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK, "Max. Einheiten pro Woche");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK, "Max. Einheiten pro Woche");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH, "Units per month");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH, "Max. Einheiten pro Monat");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH, "Max. Einheiten pro Monat");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER, "Units fror transfer");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER, "Max. Transfer von Einheiten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER, "Max. Transfer von Einheiten");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE, "Meal value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE, "Sachbezugswert Hauptmahlzeit");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE, "Sachbezugswert Hauptmahlzeit");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT, "Employer meal grant");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT, "AG Essenszuschuss");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT, "AG Essenszuschuss");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT, "Employee meal grant");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT, "Zuzahlung Sachbezugswert AN");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT, "Zuzahlung Sachbezugswert AN");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION, "Aut. adoption");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION, "Automatische Anpassung des steuerlichen DEM Wertes");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION, "Automatische Anpassung des steuerlichen DEM Wertes");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY, "Employee meal grant mandatory");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY, "Zuzahlung Sachbezugswert AN verpflichtend");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY, "Zuzahlung Sachbezugswert AN verpflichtend");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-food_voucher__main__unit", "1 unit");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-food_voucher__main__unit", "Wert DEM");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-food_voucher__main__unit", "Wert DEM");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-food_voucher__main", "Essensmarken Gutschein");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-food_voucher__main", "Essensmarken Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-food_voucher__main", "Essensmarken Gutschein");
    }

    public function up()
    {
        $this->execute("UPDATE product_group SET sort_order=sort_order+1 WHERE sort_order > 2");
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Food Voucher Service',NOW(),
							".Connection::GetSQLString(PRODUCT_GROUP__FOOD_VOUCHER).",
							'3',
							'Y'
						)");

        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__FOOD_VOUCHER));

        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Food Voucher',NOW(),
							".Connection::GetSQLString(PRODUCT__FOOD_VOUCHER__MAIN).",
							'Y'
						)");

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__FOOD_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__MONTHLY_DISCOUNT).",
                            'Discount for food voucher service',
                            '2',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION).",
                            'Salary option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT).",
                            'Employer meal grant',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT).",
                            'Employee meal grant',
                            '3',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION).",
                            'Aut. adoption',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','N','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH).",
                            'Units per month',
                            '2',
                            '".$productMain["product_id"]."',
                            '2',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER).",
                            'Units fror transfer',
                            '3',
                            '".$productMain["product_id"]."',
                            '2',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE).",
                            'Meal value',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','N','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY).",
                            'Employee meal grant mandatory',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK).",
                            'Units per week',
                            '1',
                            '".$productMain["product_id"]."',
                            '2',
							'Y','Y','Y'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__FOOD_VOUCHER__MAIN));

        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__FOOD_VOUCHER__MAIN));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__FOOD_VOUCHER));

        $this->execute("UPDATE product_group SET sort_order=sort_order-1 WHERE sort_order >= 2");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
