<?php

use Phinx\Migration\AbstractMigration;

class FoodFlexOption extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_OPTION, "Flex");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_OPTION, "Flex");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_OPTION, "Flex");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PRICE, "Flex Unit price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE, "%age of unit value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_FREE_UNITS, "Flex free units");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_FREE_UNITS, "Flexfreie Einheiten");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD__MAIN__FLEX_FREE_UNITS, "Flexfreie Einheiten");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Unit price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "%age of unit value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex-".PRODUCT__FOOD__MAIN, "Digitale Essensmarke Flex (Nutzer)");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex-".PRODUCT__FOOD__MAIN, "Digitale Essensmarke Flex (Nutzer)");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex-".PRODUCT__FOOD__MAIN, "Digitale Essensmarke Flex (Nutzer)");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__FOOD__MAIN, "Zusätzliche Essensmarken Option Flex");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__FOOD__MAIN, "Zusätzliche Essensmarken Option Flex");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__FOOD__MAIN, "Zusätzliche Essensmarken Option Flex");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__FOOD__MAIN, "Gesamter Essensmarkenwert Option Flex");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__FOOD__MAIN, "Gesamter Essensmarkenwert Option Flex");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__FOOD__MAIN, "Gesamter Essensmarkenwert Option Flex");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Sachbezug Gutschein Flex (Nutzer)");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Sachbezug Gutschein Flex (Nutzer)");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Sachbezug Gutschein Flex (Nutzer)");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__BENEFIT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__FOOD__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_OPTION).",
                            'Flex option',
                            '5',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE).",
                            'Flex unit price',
                            '6',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE).",
                            'Flex unit percentage',
                            '7',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_FREE_UNITS).",
                            'Flex free units',
                            '8',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BENEFIT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE).",
                            'Flex unit price',
                            '9',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE).",
                            'Flex unit percentage',
                            '10',
                            '".$productMain["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        $this->table("invoice_line")
            ->addColumn("flex_quantity", "integer", ["null" => true])
            ->addColumn("flex_unit_count", "float", ["null" => true])
            ->addColumn("flex_unit_price", "float", ["null" => true])
            ->addColumn("flex_unit_sum", "float", ["null" => true])
            ->addColumn("flex_amount_sum", "float", ["null" => true])
            ->addColumn("flex_unit_percentage", "float", ["null" => true])
            ->addColumn("flex_percentage_sum", "float", ["null" => true])
            ->save();

        $this->table("invoice_details")
            ->addColumn("flex_employee_units", "float", ["null" => true])
            ->addColumn("flex_free_units", "float", ["null" => true])
            ->addColumn("flex_unit_count", "float", ["null" => true])
            ->addColumn("flex_unit_price", "float", ["null" => true])
            ->addColumn("flex_unit_sum", "float", ["null" => true])
            ->addColumn("flex_unit", "float", ["null" => true])
            ->addColumn("flex_amount_sum", "float", ["null" => true])
            ->addColumn("flex_unit_percentage", "float", ["null" => true])
            ->addColumn("flex_percentage_sum", "float", ["null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_OPTION));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_FREE_UNITS));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE));

        $this->table("invoice_line")
            ->removeColumn("flex_quantity")
            ->removeColumn("flex_unit_count")
            ->removeColumn("flex_unit_price")
            ->removeColumn("flex_unit_sum")
            ->removeColumn("flex_amount_sum")
            ->removeColumn("flex_unit_percentage")
            ->removeColumn("flex_percentage_sum")
            ->save();

        $this->table("invoice_details")
            ->removeColumn("flex_employee_units")
            ->removeColumn("flex_free_units")
            ->removeColumn("flex_unit_count")
            ->removeColumn("flex_unit_price")
            ->removeColumn("flex_unit_sum")
            ->removeColumn("flex_unit")
            ->removeColumn("flex_amount_sum")
            ->removeColumn("flex_unit_percentage")
            ->removeColumn("flex_percentage_sum")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

}
