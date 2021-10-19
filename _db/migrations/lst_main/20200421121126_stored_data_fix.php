<?php

use Phinx\Migration\AbstractMigration;

class StoredDataFix extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__YEARLY_PRICE, "Quarterly service price");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__YEARLY_PRICE, "Jährlich Servicepreis");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-".OPTION__STORED_DATA__MAIN__YEARLY_PRICE, "Jährlich Servicepreis");
    }

    public function up()
    {
        //clear history
        $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));
        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM option_value_history WHERE option_id=".$option["option_id"];
        $stmt->Execute($query);

        //delete option services
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $product = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__SERVICES).",
                            'Services',
                            '1',
                            '".$product["product_id"]."',
                            '3',
							'Y','Y','N'
						)");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
