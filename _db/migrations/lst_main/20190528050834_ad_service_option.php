<?php


use Phinx\Migration\AbstractMigration;

class AdServiceOption extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-ad__main__receipt_option", "Advertisement Service Receipt Option");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-ad__main__receipt_option", "Einreichbarer Belegzeitraum");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-ad__main__receipt_option", "Einreichbarer Belegzeitraum");
    }

    public function up()
    {

        $this->execute("INSERT INTO option (option_id,type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
						VALUES (
							nextval('\"option_key_KeyID_seq\"'::regclass),
                            'string',
                            ".Connection::GetSQLString(OPTION__AD__MAIN__RECEIPT_OPTION).",
                            'Advertisement Service Receipt Option',
                            '6',
                            ".Product::GetProductIDByCode(PRODUCT__AD__MAIN).",
                            '3',
							'Y','Y','Y'
						)");
        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__AD__MAIN__RECEIPT_OPTION));

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".$optionID["option_id"].",
    						".Connection::GetSQLDate(date("01.01.2018")).",
    						1,
    						'yearly')";
        $stmt->Execute($query);

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__AD__MAIN__RECEIPT_OPTION));

        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__AD__MAIN__RECEIPT_OPTION));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

}
