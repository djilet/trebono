<?php

use Phinx\Migration\AbstractMigration;

class YearlyTotalBenefitFix extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->changeColumn("yearly_total_benefits", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->changeColumn("yearly_total_benefits", "float", ["null" => true])
            ->save();
    }
}
