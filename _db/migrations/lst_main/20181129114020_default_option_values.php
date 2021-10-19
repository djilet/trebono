<?php


use Phinx\Migration\AbstractMigration;

class DefaultOptionValues extends AbstractMigration
{
    public function up()
    {
        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";
        $values = array();
        foreach (OPTION_DEFAULT_VALUES as $key=>$value){
            $values[] = "(".Connection::GetSQLString($key).",".Connection::GetSQLString($value).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        }
        $query.= implode(",", $values);
        $this->execute($query);
    }

    public function down(){
        $query = "DELETE FROM config WHERE group_code='o_option'";

        $this->execute($query);
    }
}
