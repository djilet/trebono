<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitHistoryChangeColumn extends AbstractMigration
{
    public function up()
    {
        $this->table('company_unit_history')
            ->changeColumn("value", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->table('company_unit_history')
            ->changeColumn("value", "string", ["null" => false])
            ->save();
    }
}
