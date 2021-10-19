<?php

use Phinx\Migration\AbstractMigration;

class CreateBillableItemTable extends AbstractMigration
{

    public function up()
    {

        $this->table("billable_item", ["id" => "item_id"])
            ->addColumn("date_start", "date", ["null" => false])
            ->addColumn("date_end", "date", ["null" => false])
            ->addColumn("created", "datetime", ["null" => false])
            ->addColumn("created_user", "integer", ["null" => false])
            ->addColumn("company_id", "integer", ["null" => false])
            ->addColumn("price", "float", ["null" => false])
            ->addColumn("item_name", "string", ["length" => 255, "null" => false])
            ->addColumn("discount", "float", ["null" => false])
            ->addColumn("quantity", "integer", ["null" => false])    
            // ->addColumn("archive", "flag", ["null" => false])
            // ->addColumn("in_invoice", "flag", ["null" => false])
            ->addColumn("updated_at", "timestamp", ["null" => false])    
            ->create();
    }


    public function down()
    {
        $this->table("billable_item")->drop()->save();
    }
}
