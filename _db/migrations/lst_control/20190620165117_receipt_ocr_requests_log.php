<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class ReceiptOcrRequestsLog extends AbstractMigration
{
    public function up()
    {
        $this->table("ocr_request", ["id" => "request_id"])
        ->addColumn("created", "timestamp", ["null" => false])
        ->addColumn("url", "string", ["null" => false, "length" => 255])
        ->addColumn("response_time", "integer", ["null" => false])
        ->addColumn("type", "string", ["null" => false, "length" => 255])
        ->addColumn("is_successful", Literal::from("flag"), ["null" => false])
        ->addColumn("is_receipt", Literal::from("flag"), ["null" => true])
        ->addColumn("receipt_id", "integer", ["null" => false])
        ->addColumn("user_id", "integer", ["null" => false])
        ->save();
    }
    
    public function down()
    {
        $this->dropTable("ocr_request");
    }
}
