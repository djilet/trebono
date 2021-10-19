<?php


use Phinx\Migration\AbstractMigration;

class SignatureFields extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt_file")
        ->addColumn("signature_file", "string", ["length" => 255, "null" => true])
        ->addColumn("signature_report_file", "string", ["length" => 255, "null" => true])
        ->addColumn("signature_status", "string", ["length" => 50, "null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("receipt_file")
        ->removeColumn("signature_file")
        ->removeColumn("signature_report_file")
        ->removeColumn("signature_status")
        ->save();
    }
}
