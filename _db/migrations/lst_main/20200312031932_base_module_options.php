<?php

use Phinx\Migration\AbstractMigration;

class BaseModuleOptions extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-base__main__deactivation_reason", "Deactivation reason");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-base__main__deactivation_reason", "Deaktivierungsgrund");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-base__main__deactivation_reason", "Deaktivierungsgrund");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__BASE__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
					VALUES (
						nextval('\"option_key_KeyID_seq\"'::regclass),
                        'string',
                        ".Connection::GetSQLString(OPTION__BASE__MAIN__DEACTIVATION_REASON).",
                        'Deactivation reason',
                        '2',
                        '".$productMain["product_id"]."',
                        '3',
						'N','N','Y'
					)");

        $this->execute("UPDATE option SET level_global='Y'
                    WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__MONTHLY_DISCOUNT));

        $this->execute("UPDATE option SET level_global='Y'
                    WHERE code=".Connection::GetSQLString(	OPTION__BASE__MAIN__IMPLEMENTATION_DISCOUNT));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $option= $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__DEACTIVATION_REASON));

        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id='".$option["option_id"]."'";
        $stmt->Execute($query);

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__DEACTIVATION_REASON));

        $this->execute("UPDATE option SET level_global='N'
                    WHERE code=".Connection::GetSQLString(OPTION__BASE__MAIN__MONTHLY_DISCOUNT));

        $this->execute("UPDATE option SET level_global='N'
                    WHERE code=".Connection::GetSQLString(	OPTION__BASE__MAIN__IMPLEMENTATION_DISCOUNT));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
