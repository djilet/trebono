<?php

use Phinx\Migration\AbstractMigration;

class AddReceiptVerificationHistoryTable extends AbstractMigration
{
    public function up()
    {
        $this->table('receipt_verification_history', ["id" => "verification_id"])
            ->addColumn("receipt_id", "integer", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("opening_status", "string", ["null" => false])
            ->addColumn("saved_status", "string", ["null" => false])
            ->addColumn("amount", "float", ["null" => true])
            ->addColumn("opening_receipt_at", "timestamp", ["null" => false])
            ->addColumn("saved_receipt_at", "timestamp", ["null" => false])
            ->addColumn("created_at", "timestamp", ["null" => false])
            ->create();
    }

    public function down()
    {
        $this->table("receipt_verification_history")->drop()->save();
    }
}
