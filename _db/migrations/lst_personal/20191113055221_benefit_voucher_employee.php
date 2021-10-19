<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherEmployee extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->addColumn("bank_name", "string", ["length" => 255, "null" => true])
            ->addColumn("iban", "string", ["length" => 255, "null" => true])
            ->addColumn("bic", "string", ["length" => 255, "null" => true])
            ->addColumn("creditor_number", "string", ["length" => 255, "null" => true])
            ->save();
    }

    public function down()
    {
        $this->table("employee")
            ->removeColumn("bank_name")
            ->removeColumn("iban")
            ->removeColumn("bic")
            ->removeColumn("creditor_number")
            ->save();
    }
}
