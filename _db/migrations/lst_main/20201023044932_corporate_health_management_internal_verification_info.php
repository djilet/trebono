<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementInternalVerificationInfo extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO, "Internal verification information");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO, "Interne Verifizierungsinformationen");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO, "Interne Verifizierungsinformationen");
    }

    public function up()
    {
        $maxSortOrder = $this->fetchRow("SELECT MAX(sort_order) as sort_order FROM option WHERE product_id=".Product::GetProductIDByCode(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN));
        $sortOrder = $maxSortOrder["sort_order"] + 1;

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                    VALUES (
                        nextval('\"option_key_KeyID_seq\"'::regclass),
                        'string',
                        ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO).",
                        'Internal verification information',
                        $sortOrder,
                        '".Product::GetProductIDByCode(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN)."',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__INTERNAL_VERIFICATION_INFO));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
