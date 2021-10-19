<?php

use Phinx\Migration\AbstractMigration;

class NewYearEmployeeReport extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->addColumn("yearly_total_benefits", "float", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("yearly_total_benefits")
            ->save();
    }
}
