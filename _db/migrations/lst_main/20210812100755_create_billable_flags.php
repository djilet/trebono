<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;
class CreateBillableFlags extends AbstractMigration
{
    public function up()
    {
        $this->table("billable_item")
        ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "Y"])
        ->addColumn("in_invoice", Literal::from("flag"), ["null" => false, "default" => "N"])
        ->save();
    }
    
    public function down()
    {
        $this->table("billable_item")
        ->removeColumn("archive")
        ->save();

        $this->table("billable_item")
        ->removeColumn("in_invoice")
        ->save();
    }
}
