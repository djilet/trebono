<?php

use Phinx\Migration\AbstractMigration;

class YearlyReportHistory extends AbstractMigration
{
    public function up()
    {
        $this->table("yearly_employee_report_history", ["id" => "value_id"])
            ->addColumn("report_id", "integer", ["null" => false])
            ->addColumn("property_name", "text", ["null" => false])
            ->addColumn("value", "text", ["null" => false])
            ->addColumn("user_id", "integer", ["null" => false])
            ->addColumn("created", "timestamp", ["null" => false])
            ->addColumn("created_from", "text", ["null" => false])
            ->save();
    }

    public function down()
    {
        $this->table("yearly_employee_report_history")
            ->drop()
            ->save();
    }
}
