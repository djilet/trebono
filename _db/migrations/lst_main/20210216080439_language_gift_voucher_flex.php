<?php

use Phinx\Migration\AbstractMigration;

class LanguageGiftVoucherFlex extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION, "Flex");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION, "Flex");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION, "Flex");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Unit price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE, "Flex Stückpreis");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "%age of unit value");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE, "% Alter des Einheitswertes");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex-".PRODUCT__GIFT_VOUCHER__MAIN, "Geschenk Gutschein Flex (Nutzer)");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex-".PRODUCT__GIFT_VOUCHER__MAIN, "Geschenk Gutschein Flex (Nutzer)");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex-".PRODUCT__GIFT_VOUCHER__MAIN, "Geschenk Gutschein Flex (Nutzer)");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__GIFT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__GIFT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_unit_count-".PRODUCT__GIFT_VOUCHER__MAIN, "Anzahl Gutscheine Option Flex");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__GIFT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__GIFT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "product_flex_total_amount-".PRODUCT__GIFT_VOUCHER__MAIN, "Gutschein Wert Gesamt");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__GIFT_VOUCHER__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'flag',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION).",
                            'Flex option',
                            '5',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','Y'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE).",
                            'Flex unit price',
                            '6',
                            '".$productMain["product_id"]."',
                            '1',
							'Y','Y','N'
						)");

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'int',
                            ".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE).",
                            'Flex unit percentage',
                            '7',
                            '".$productMain["product_id"]."',
                            '1',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_OPTION));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
