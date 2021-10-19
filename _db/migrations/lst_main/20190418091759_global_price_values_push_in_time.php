<?php


use Phinx\Migration\AbstractMigration;

class GlobalPriceValuesPushInTime extends AbstractMigration
{
    public function up()
    {
        $stmt = GetStatement(DB_CONTROL);
        $optionList = $this->fetchAll("SELECT code FROM option WHERE title='Implementation fee' OR title='Monthly service price'");
        foreach($optionList as $option)
        {
            $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($option["code"]));
            $query = "SELECT value FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." ORDER BY created DESC";
            $value = $stmt->FetchRow($query);

            $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".intval($optionID["option_id"]).",
    						".Connection::GetSQLDate(date("01.01.2018")).",
    						1,
    						".$value["value"].")";

            $stmt->Execute($query);
        }
    }

    public function down()
    {
        $stmt = GetStatement(DB_CONTROL);
        $optionList = $this->fetchAll("SELECT code FROM option WHERE title='Implementation fee' OR title='Monthly service price'");
        foreach($optionList as $option)
        {
            $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($option["code"]));
            $query = "SELECT value FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." ORDER BY created DESC";
            $value = $stmt->FetchRow($query);

            $values[] = "(".Connection::GetSQLString($option["code"]).",".$value["value"].", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";

            $query = "DELETE FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." AND created=".Connection::GetSQLDate(date("01.01.2018"));
            $stmt->Execute($query);
        }
    }
}
