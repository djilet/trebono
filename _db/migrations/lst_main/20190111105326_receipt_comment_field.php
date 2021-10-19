<?php


use Phinx\Migration\AbstractMigration;

class ReceiptCommentField extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt")
        ->addColumn("comment", "text", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("receipt")
        ->removeColumn("comment")
        ->save();
    }
}
