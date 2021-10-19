<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-group-benefit_voucher", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-group-benefit_voucher", "Sachbezug Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-group-benefit_voucher", "Sachbezug Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-benefit_voucher__main", "Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-benefit_voucher__main", "Sachbezug Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-benefit_voucher__main", "Sachbezug Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__monthly_price", "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__monthly_price", "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__monthly_price", "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__monthly_discount", "Discount for benefit voucher service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__monthly_discount", "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__monthly_discount", "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__implementation_price", "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__implementation_price", "Implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__implementation_price", "Implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__implementation_discount", "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__implementation_discount", "Rabatt für implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__implementation_discount", "Rabatt für implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. Monthly Value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. monatl. Wert");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__max_monthly", "Max. monatl. Wert");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit_voucher__main__salary_option", "Salary option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit_voucher__main__salary_option", "Gehaltsoption");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit_voucher__main__salary_option", "Gehaltsoption");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-benefit__main__payroll_export", "Payroll export");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-benefit__main__payroll_export", "Export der Gehaltsabrechnung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-benefit__main__payroll_export", "Export der Gehaltsabrechnung");
    }

    public function up()
    {
        $this->execute("UPDATE product_group SET sort_order=sort_order+1 WHERE sort_order > 3");
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Benefit Voucher Service',NOW(),
							".Connection::GetSQLString(PRODUCT_GROUP__BENEFIT_VOUCHER).",
							'4',
							'Y'
						)");

        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__BENEFIT_VOUCHER));

        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Benefit Voucher',NOW(),
							".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN).",
							'Y'
						)");

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY).",
                            'Max. Monthly Value',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__MONTHLY_DISCOUNT).",
                            'Discount for benefit voucher service',
                            '2',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT).",
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
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__SALARY_OPTION).",
                            'Salary option',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__PAYROLL_EXPORT).",
                            'Payroll export',
                            '3',
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
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__BENEFIT_VOUCHER));

        $this->execute("UPDATE product_group SET sort_order=sort_order-1 WHERE sort_order >= 4");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
