<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitAddColumn extends AbstractMigration
{
    public function up()
    {
        $this->table("company_unit")->addColumn("master_data_export_id", "integer", ["null" => true])->save();
    }

    public function down()
    {
        $this->table("company_unit")->removeColumn("master_data_export_id");
    }
}
