<?php


use Phinx\Migration\AbstractMigration;

class PayrollFlags extends AbstractMigration
{
    public function change(){
        $this->table("receipt")
            ->addColumn("datev_export", "string", ["default" => '0'])
            ->addColumn("pdf_export", "string", ["default" => '0'])
            ->save();

        $table = $this->table("payroll",['id' => 'payroll_id']);
        $table->addColumn("company_unit_id", "integer")
            ->addColumn("payroll_month", "string")
            ->addColumn("type", "string")
            ->addColumn("created", "datetime")
            ->addColumn("file", "string")
            ->create();
    }
}
