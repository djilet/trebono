<?php

use Phinx\Migration\AbstractMigration;

class FoodAndFoodVoucherNewFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD__MAIN__IMPORTANT_INFO, "Important Information about food service usage");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD__MAIN__IMPORTANT_INFO, "Wichtige Informationen zur Verwendung von Lebensmittelservices");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD__MAIN__IMPORTANT_INFO, "Wichtige Informationen zur Verwendung von Lebensmittelservices");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO, "Important Information about food voucher service usage");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO, "Wichtige Informationen zur Nutzung des Service für Lebensmittelgutscheine");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO, "Wichtige Informationen zur Nutzung des Service für Lebensmittelgutscheine");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ImportantInfoFood", "Important Information about food service usage");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ImportantInfoFood", "Wichtige Informationen zur Verwendung von Lebensmittelservices");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ImportantInfoFood", "Wichtige Informationen zur Verwendung von Lebensmittelservices");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ImportantInfoFoodVoucher", "Important Information about food voucher service usage");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ImportantInfoFoodVoucher", "Wichtige Informationen zur Nutzung des Service für Lebensmittelgutscheine");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ImportantInfoFoodVoucher", "Wichtige Informationen zur Nutzung des Service für Lebensmittelgutscheine");
    }

    public function up()
    {
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__FOOD__MAIN__IMPORTANT_INFO).",
                            'Important Information about food service usage',
                            '6',
                            '".Product::GetProductIDByCode(PRODUCT__FOOD__MAIN)."',
                            '3',
							'N','N','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO).",
                            'Important Information about food voucher service usage',
                            '9',
                            '".Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN)."',
                            '3',
							'N','N','Y'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__IMPORTANT_INFO));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
