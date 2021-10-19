<?php


use Phinx\Migration\AbstractMigration;

class SalaryOptionType extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "Z", "Z");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "Z", "Z");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "Z", "Z");

        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "W", "W");
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "W", "W");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "W", "W");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        $stmt = GetStatement(DB_CONTROL);
        $optionList = $this->fetchAll("SELECT code FROM option WHERE title='Salary option'");
        foreach($optionList as $option)
        {
            $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($option["code"]));
            $query = "INSERT INTO option_value_history (level, entity_id, option_id, created, user_id, value) VALUES (
                            ".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
                            0,
    						".intval($optionID["option_id"]).",
    						".Connection::GetSQLDate(date("01.01.2018")).",
    						1,
    						'Z')";

            $stmt->Execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

        $stmt = GetStatement(DB_CONTROL);
        $optionList = $this->fetchAll("SELECT code FROM option WHERE title='Salary option'");
        foreach($optionList as $option)
        {
            $optionID = $this->fetchRow("SELECT option_id FROM option WHERE code=".Connection::GetSQLString($option["code"]));

            $query = "DELETE FROM option_value_history WHERE option_id='".$optionID["option_id"]."' AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." AND created=".Connection::GetSQLDate(date("01.01.2018"));
            $stmt->Execute($query);
        }
    }
}
