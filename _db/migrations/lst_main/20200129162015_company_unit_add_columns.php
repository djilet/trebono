<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddColumns extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")->addColumn("master_data_sepa_service_id", "integer", ["null" => true])->save();
        $this->table("company_unit")->addColumn("master_data_sepa_voucher_id", "integer", ["null" => true])->save();
    }

    public function down()
    {
        $this->table("company_unit")->removeColumn("master_data_sepa_service_id")->save();
        $this->table("company_unit")->removeColumn("master_data_sepa_voucher_id")->save();
    }
}
