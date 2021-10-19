<?php


use Phinx\Migration\AbstractMigration;

class ReceiptFileLog extends AbstractMigration
{
    public function up()
    {   
        $this->table("receipt_file_log", ["id" => "receipt_file_id"])
        ->addColumn("updated", "timestamp", ["null" => false])
        ->addColumn("content", "text", ["null" => false])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("receipt_file_log");
    }
}
