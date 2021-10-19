<?php

use Phinx\Migration\AbstractMigration;

class NewFieldsContractualInformation extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION, "Contractual information Internet");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION, "Vertragsinformationen Internet");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION, "Vertragsinformationen Internet");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION, "Contractual information Mobile");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION, "Vertragsinformationen Mobil");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION, "Vertragsinformationen Mobil");
    }

    public function up()
    {
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION).",
                            'Contractual information Internet',
                            '6',
                            '".Product::GetProductIDByCode(PRODUCT__INTERNET__MAIN)."',
                            '3',
							'Y','Y','Y'
						)");
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION).",
                            'Contractual information Mobile',
                            '6',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION));
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
