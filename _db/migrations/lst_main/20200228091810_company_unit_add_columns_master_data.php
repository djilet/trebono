<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddColumnsMasterData extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")->addColumn("master_data_service_update_id", "integer", ["null" => true, "default" => 0])->save();
        $this->table("company_unit")->addColumn("master_data_voucher_update_id", "integer", ["null" => true, "default" => 0])->save();
        $this->table("company_unit")->addColumn("master_data_sepa_service_update_id", "integer", ["null" => true, "default" => 0])->save();
        $this->table("company_unit")->addColumn("master_data_sepa_voucher_update_id", "integer", ["null" => true, "default" => 0])->save();
    }

    public function down()
    {
        $this->table("company_unit")->removeColumn("master_data_service_update_id")->save();
        $this->table("company_unit")->removeColumn("master_data_voucher_update_id")->save();
        $this->table("company_unit")->removeColumn("master_data_sepa_service_update_id")->save();
        $this->table("company_unit")->removeColumn("master_data_sepa_voucher_update_id")->save();
    }
}
