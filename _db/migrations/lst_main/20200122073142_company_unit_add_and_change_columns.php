<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddAndChangeColumns extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")->removeColumn("master_data_export_id")->save();
        $this->table("company_unit")->addColumn("master_data_service_id", "integer", ["null" => true])->save();
        $this->table("company_unit")->addColumn("master_data_voucher_id", "integer", ["null" => true])->save();
    }

    public function down()
    {
        $this->table("company_unit")->addColumn("master_data_export_id", "integer", ["null" => true])->save();
        $this->table("company_unit")->removeColumn("master_data_service_id")->save();
        $this->table("company_unit")->removeColumn("master_data_voucher_id")->save();
    }
}
