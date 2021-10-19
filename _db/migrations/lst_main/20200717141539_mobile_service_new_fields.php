<?php

use Phinx\Migration\AbstractMigration;

class MobileServiceNewFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_MODEL, "Mobile model");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_MODEL, "Mobiles modell");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_MODEL, "Mobiles modell");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_NUMBER, "Mobile number");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_NUMBER, "Handynummer");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__MOBILE_NUMBER, "Handynummer");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "MobileModel", "Mobile model");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "MobileModel", "Mobiles modell");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "MobileModel", "Mobiles modell");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "MobileNumber", "Mobile number");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "MobileNumber", "Handynummer");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "MobileNumber", "Handynummer");
    }

    public function up()
    {
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__MOBILE__MAIN__MOBILE_MODEL).",
                            'Mobile model',
                            '7',
                            '".Product::GetProductIDByCode(PRODUCT__MOBILE__MAIN)."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__MOBILE__MAIN__MOBILE_NUMBER).",
                            'Mobile number',
                            '8',
                            '".Product::GetProductIDByCode(PRODUCT__MOBILE__MAIN)."',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__MOBILE__MAIN__MOBILE_MODEL));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__MOBILE__MAIN__MOBILE_NUMBER));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
