<?php

use Phinx\Migration\AbstractMigration;

class RecreationServiceLimitMessage extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "option-recreation__main__limit_message", "Limit message");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "option-recreation__main__limit_message", "Nachricht begrenzen");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "option-recreation__main__limit_message", "Nachricht begrenzen");
    }

    public function up()
    {
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__RECREATION__MAIN));

        $this->execute("INSERT INTO option (type,code,title,sort_order,product_id,group_id,level_global,level_company_unit,level_employee)
                        VALUES (
                            'string',
                            ".Connection::GetSQLString(OPTION__RECREATION__MAIN__LIMIT_MESSAGE).",
                            'Limit message',
                            '9',
                            '".$productMain["product_id"]."',
                            '3',
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
        $this->execute("DELETE FROM option WHERE code=".Connection::GetSQLString(OPTION__RECREATION__MAIN__LIMIT_MESSAGE));

        $stmt = GetStatement(DB_CONTROL);
        $stmt->execute("DELETE FROM option_value_history WHERE option_id=".Option::GetOptionIDByCode(OPTION__RECREATION__MAIN__LIMIT_MESSAGE));

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
