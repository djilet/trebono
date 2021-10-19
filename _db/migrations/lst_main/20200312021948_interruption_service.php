<?php

use Phinx\Migration\AbstractMigration;

class InterruptionService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "product-base__interruption", "Interruption Service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "product-base__interruption", "Unterbrechung");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "product-base__interruption", "Unterbrechung");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-base__interruption__monthly_price", "Monthly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-base__interruption__monthly_price", "Monatlicher Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-base__interruption__monthly_price", "Monatlicher Servicepreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-base__interruption__monthly_discount", "Discount for interruption service");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-base__interruption__monthly_discount", "Rabatt für Unterbrechungsservice");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-base__interruption__monthly_discount", "Rabatt für Unterbrechungsservice");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-base__interruption__implementation_price", "Implementation fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-base__interruption__implementation_price", "Implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-base__interruption__implementation_price", "Implementierungspreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-base__interruption__implementation_discount", "Discount for implem. fee");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-base__interruption__implementation_discount", "Rabatt für implementierungspreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-base__interruption__implementation_discount", "Rabatt für implementierungspreis");
    }

    public function up()
    {
        $group = $this->fetchRow("SELECT group_id FROM product_group WHERE code=".Connection::GetSQLString(PRODUCT_GROUP__BASE));

        $this->execute("INSERT INTO product (product_id,group_id,title,created,code)
						VALUES (
							nextval('\"product_ProductID_seq\"'::regclass),
                            '".$group["group_id"]."',
							'Interruption',NOW(),
							".Connection::GetSQLString(PRODUCT__BASE__INTERRUPTION)."
						)");

        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BASE__INTERRUPTION));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BASE__INTERRUPTION__MONTHLY_PRICE).",
                            'Monthly service price',
                            '1',
                            '".$product["product_id"]."',
                            '1',
							'Y','N','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BASE__INTERRUPTION__MONTHLY_DISCOUNT).",
                            'Discount for interruption service',
                            '2',
                            '".$product["product_id"]."',
                            '1',
							'Y','N','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BASE__INTERRUPTION__IMPLEMENTATION_PRICE).",
                            'Implementation fee',
                            '3',
                            '".$product["product_id"]."',
                            '1',
							'Y','N','N'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BASE__INTERRUPTION__IMPLEMENTATION_DISCOUNT).",
                            'Discount for implem. fee',
                            '4',
                            '".$product["product_id"]."',
                            '1',
							'Y','N','N'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BASE__INTERRUPTION));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$product["product_id"]."'");

        $stmt = GetStatement(DB_CONTROL);
        foreach($optionList as $option)
        {
            $query = "DELETE FROM option_value_history WHERE option_id='".$option["option_id"]."'";
            $stmt->Execute($query);
        }

        $this->execute("DELETE FROM option WHERE product_id='".$product["product_id"]."'");
        $this->execute("DELETE FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BASE__INTERRUPTION));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
