<?php


use Phinx\Migration\AbstractMigration;

class BonusModuleDefaultValues extends AbstractMigration
{
    public function up()
    {
        $query = "INSERT INTO config (code, value, group_code, editor, updated) VALUES";
        
        $values = array();

        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE).",".Connection::GetSQLString(2).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        if(!$this->fetchRow("SELECT code FROM config WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE)))
            $values[] = "(".Connection::GetSQLString(OPTION__BONUS__MAIN__MONTHLY_PRICE).",".Connection::GetSQLString(10).", 'o_option','field-float', ".Connection::GetSQLString(GetCurrentDateTime()).")";
        
        $query.= implode(",", $values);
        if($values != array())
            $this->execute($query);
    }
    
    public function down(){
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__IMPLEMENTATION_PRICE);
        
        $this->execute($query);
        
        $query = "DELETE FROM config WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__MONTHLY_PRICE);
        
        $this->execute($query);
    }
}
