<?php

use Phinx\Migration\AbstractMigration;

class AllServicesNewField extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        foreach(OPTIONS_INTERNAL_VERIFICATION_INFO as $optionCode)
        {
            $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".$optionCode, "Internal verification information");
            $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".$optionCode, "Interne Verifizierungsinformationen");
            $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".$optionCode, "Interne Verifizierungsinformationen");
        }

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "InternalVerificationInfo", "Internal verification information");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "InternalVerificationInfo", "Interne Verifizierungsinformationen");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "InternalVerificationInfo", "Interne Verifizierungsinformationen");
    }

    public function up()
    {
        foreach(OPTIONS_INTERNAL_VERIFICATION_INFO as $productCode => $optionCode)
        {
            $maxSortOrder = $this->fetchRow("SELECT MAX(sort_order) as sort_order FROM option WHERE product_id=".Product::GetProductIDByCode($productCode));
            $sortOrder = $maxSortOrder["sort_order"] + 1;

            $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString($optionCode).",
                            'Internal verification information',
                            $sortOrder,
                            '".Product::GetProductIDByCode($productCode)."',
                            '3',
							'N','N','Y'
						)");
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach(OPTIONS_INTERNAL_VERIFICATION_INFO as $optionCode)
        {
            $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString($optionCode));
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
