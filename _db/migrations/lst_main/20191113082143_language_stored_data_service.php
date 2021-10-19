<?php

use Phinx\Migration\AbstractMigration;

class LanguageStoredDataService extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "Quarterly", "Quarterly");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "Quarterly", "quartal");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "Quarterly", "quartal");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        //delete all option's values stored data service
        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."'");

        $stmt = GetStatement(DB_CONTROL);
        foreach($optionList as $option)
        {
            $query = "DELETE FROM option_value_history WHERE option_id='".$option["option_id"]."'";
            $stmt->Execute($query);
        }

        //insert default global value only for emloyees with root user
        $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString(OPTION__STORED_DATA__MAIN__EMPLOYEES));
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
                ".intval($optionID["option_id"]).",
                ".Connection::GetSQLDate(GetCurrentDateTime()).",
                1,
                'all')";        
        $stmt->Execute($query);
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $productMain = $this->fetchRow("SELECT product_id FROM product WHERE code=".Connection::GetSQLString(PRODUCT__STORED_DATA__MAIN));
        $optionList = $this->fetchAll("SELECT option_id FROM option WHERE product_id='".$productMain["product_id"]."'");

        $stmt = GetStatement(DB_CONTROL);
        foreach($optionList as $option)
        {
            $query = "DELETE FROM option_value_history WHERE option_id='".$option["option_id"]."'";
            $stmt->Execute($query);
        }
    }
}
