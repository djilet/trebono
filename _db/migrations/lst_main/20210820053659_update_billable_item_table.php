<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class UpdateBillableItemTable extends AbstractMigration
{
    public function up()
    {
        $this->table("billable_item")
            ->renameColumn("company_id", "company_unit_id")
            ->removeColumn("in_invoice")
            ->addColumn("invoice_id", "integer", ["null" => true, "default" => null])
            ->save();
    }

    public function down()
    {
        $this->table("billable_item")
            ->renameColumn("company_unit_id", "company_id")
            ->removeColumn("invoice_id")
            ->addColumn("in_invoice", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();
    }
}
