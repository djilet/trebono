<?php


use Phinx\Migration\AbstractMigration;

class ContractEndDateChange extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit_contract")
            ->addColumn("end_date_created", "timestamp", ["null" => true])
            ->save();

        $this->table("employee_contract")
            ->addColumn("end_date_created", "timestamp", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("company_unit_contract")
            ->removeColumn("end_date_created")
            ->save();

        $this->table("employee_contract")
            ->removeColumn("end_date_created")
            ->save();
    }
}
