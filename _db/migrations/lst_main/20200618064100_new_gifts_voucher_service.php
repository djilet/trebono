<?php

use Phinx\Migration\AbstractMigration;

class NewGiftsVoucherService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-group-".PRODUCT_GROUP__GIFT_VOUCHER, "Gift Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-group-".PRODUCT_GROUP__GIFT_VOUCHER, "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-group-".PRODUCT_GROUP__GIFT_VOUCHER, "Geschenk Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-".PRODUCT__GIFT_VOUCHER__MAIN, "Gift Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-".PRODUCT__GIFT_VOUCHER__MAIN, "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-".PRODUCT__GIFT_VOUCHER__MAIN, "Geschenk Gutschein");


        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE, "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE, "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Discount for gift voucher service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT, "Rabatt für monatlichen Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE, "Implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT, "Rabatt für implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION, "Salary option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION, "Gehaltsoption");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION, "Gehaltsoption");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER, "Max. amount per voucher");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER, "Max. Gutscheinwert");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER, "Max. Gutscheinwert");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR, "Max number of vouchers per year");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR, "Max. Anzahl an Gutscheinen pro Jahr");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR, "Max. Anzahl an Gutscheinen pro Jahr");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product-gift_voucher__main", "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product-gift_voucher__main", "Geschenk Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product-gift_voucher__main", "Geschenk Gutschein");
    }

    public function up()
    {
        $this->execute("UPDATE product_group SET sort_order=sort_order+1 WHERE sort_order > 10");
        $this->execute("INSERT INTO product_group (group_id,title,created,code,sort_order,receipts,voucher)
						VALUES (
							nextval('\"product_group_GroupID_seq\"'::regclass),
							'Gift Voucher Service',NOW(),
							".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER).",
							'11',
							'Y',
							'Y'
						)");

        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));

        $this->execute("INSERT INTO product (product_id,group_id,title,created,code,base_for_api)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Gift Voucher',NOW(),
							".Connection::GetSQLString(PRODUCT__GIFT_VOUCHER__MAIN).",
							'Y'
						)");

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__GIFT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__MONTHLY_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__MONTHLY_DISCOUNT).",
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
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_PRICE).",
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
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__IMPLEMENTATION_DISCOUNT).",
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
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__SALARY_OPTION).",
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
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER).",
                            'Max amount per voucher',
                            '1',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR).",
                            'Max qty of vouchers per year',
                            '2',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__GIFT_VOUCHER__MAIN));

        $this->execute("DELETE FROM option WHERE product_id='".$productMain["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__GIFT_VOUCHER__MAIN));
        $this->execute("DELETE FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__GIFT_VOUCHER));

        $this->execute("UPDATE product_group SET sort_order=sort_order-1 WHERE sort_order >= 11");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
