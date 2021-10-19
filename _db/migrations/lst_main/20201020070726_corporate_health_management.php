<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagement extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-group-".PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT, "Corporate Health Management");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-group-".PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT, "Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-group-".PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT, "Betriebliches Gesundheitsmanagement");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Corporate Health Management");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Betriebliches Gesundheitsmanagement");


        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE, "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT, "Discount for CHM service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE, "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT, "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION, "Salary option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION, "Gehaltsoption");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION, "Gehaltsoption");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY, "Max. yearly Budget");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY, "Max. Jahresbudget");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY, "Max. Jahresbudget");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT, "Payroll export");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT, "Export in die Gehaltsabrechnung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT, "Export in die Gehaltsabrechnung");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Betriebliches Gesundheitsmanagement");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-".PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN, "Betriebliches Gesundheitsmanagement");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-confirmation_description", "The document was recorded according to the organizational instructions. If another amount should be included in the receipt, please do not confirm, but send us a message here in voucher chat.");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-confirmation_description", "Der Beleg wurde entsprechend der Organisationsanweisung aufgenommen. Sollte ein anderer Betrag im Beleg enthalten sein, bestätigen Sie bitte nicht, sondern schreiben uns hier im Beleg-Chat eine Nachricht.");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-receipt_approve_by_employee_success", "Please destroy your receipt now");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT."-api-receipt_approve_by_employee_success", "Bitte vernichten Sie den Beleg oder schreiben 'Kopie' darauf! Sie dürfen diesen Beleg nun nicht weiter verwenden (z.B. bei der Steuer einreichen, etc.) Vielen Dank dafür, Ihr trebono Team.");
    }

    public function up()
    {
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts,multiple_receipt_file)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Corporate Health Management Service',
							NOW(),
							".Connection::GetSQLString(PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT).",
							'18',
							'Y',
							'Y'
						)");

        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT));

        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Corporate Health Management',NOW(),
							".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN).",
							'Y'
						)");

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MONTHLY_DISCOUNT).",
                            'Discount for corporate health management service',
                            '2',
                            '".$productMain["product_id"]."',
                            '1',
							'N','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__IMPLEMENTATION_DISCOUNT).",
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
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION).",
                            'Salary option',
                            '3',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY).",
                            'Max. yearly Budget',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT).",
                            'Payroll export',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
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
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN));

        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
