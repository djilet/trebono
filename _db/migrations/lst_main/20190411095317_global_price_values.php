<?php


use Phinx\Migration\AbstractMigration;

class GlobalPriceValues extends AbstractMigration
{

    public function up()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'Y')
            ->where(['code' => OPTION__BENEFIT__MAIN__RECEIPT_OPTION])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'Y')
            ->where(['OR' => ['title >' => 'Implementation fee', 'title' => 'Monthly service price']])
            ->execute();

        $stmt = GetStatement(DB_CONTROL);
        $configList = $this->fetchAll("SELECT code, value FROM config WHERE group_code='o_option'");
        foreach($configList as $config)
        {
            $option = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($config["code"]));

            $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".intval($option["option_id"]).",
    						".Connection::GetSQLDate(GetCurrentDateTime()).",
    						1,
    						".$config["value"].")";

            $stmt->Execute($query);
        }
        $this->execute("DELETE FROM config WHERE group_code='o_option'");
    }

    public function down()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'N')
            ->where(['code' => OPTION__BENEFIT__MAIN__RECEIPT_OPTION])
            ->execute();

        $builder = $this->getQueryBuilder();
        $builder
            ->update('option')
            ->set('level_global', 'N')
            ->where(['OR' => ['title >' => 'Implementation fee', 'title' => 'Monthly service price']])
            ->execute();

        $values = array();

        $stmt = GetStatement(DB_CONTROL);
        $optionList = $this->fetchAll("SELECT code FROM option WHERE title='Implementation fee' OR title='Monthly service price'");
        foreach($optionList as $option)
        {
            $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($option["code"]));
            $query = "SELECT value FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." ORDER BY created DESC";
            $value = $stmt->FetchRow($query);

            $values[] = "(".Connection::GetSQLString($option["code"]).",".$value["value"].", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";

            $query = "DELETE FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL);
            $stmt->Execute($query);
        }

        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES ".implode(",", $values);

        if($values != array())
            $this->execute($query);
    }
}
