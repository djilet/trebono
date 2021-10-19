<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ProductInheritableFlag extends AbstractMigration
{
    public function up()
    {
        $this->table("product")
        ->addColumn("inheritable", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
        
        $this->execute("UPDATE product SET inheritable='Y' WHERE code LIKE '%__advanced_security'");
    }
    
    public function down()
    {
        $this->table("product")
        ->removeColumn("inheritable")
        ->save();
    }
}
