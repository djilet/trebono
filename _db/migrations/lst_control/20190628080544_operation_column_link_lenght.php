<?php


use Phinx\Migration\AbstractMigration;

class OperationColumnLinkLenght extends AbstractMigration
{
    public function up()
    {
        $this->table("operation")
        ->changeColumn("link", "text")
        ->save();
    }
    
    public function down()
    {
        $this->table("operation")
        ->changeColumn("link", "string", ["lenght" => "255"])
        ->save();
    }
}
