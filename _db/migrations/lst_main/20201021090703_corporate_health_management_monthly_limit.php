<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthManagementMonthlyLimit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY, "Max. monthly Budget");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY, "Max. Budget pro Monat");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY, "Max. Budget pro Monat");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN));

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'currency',
                            ".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY).",
                            'Max. monthly Budget',
                            '1',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
