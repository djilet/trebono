<?php

use Phinx\Migration\AbstractMigration;

class AddIndexToReceiptHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("receipt_history")
            ->addIndex(["receipt_id"], ["name" => "receipt_id_index"])
            ->save();
    }

    public function down()
    {
        $this->table("receipt_history")
            ->removeIndexByName("receipt_id_index")
            ->save();
    }
}
