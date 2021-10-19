<?php


use Phinx\Migration\AbstractMigration;

class GivveInfoRemove extends AbstractMigration
{
    public function up()
    {
        $this->table("employee")
            ->removeColumn("givve_login")
            ->removeColumn("givve_password")
            ->save();

        $this->table("givve_voucher")->drop()->save();
        $this->table("givve_voucher_transaction")->drop()->save();
    }

    public function down()
    {
        $this->table("employee")
            ->addColumn("givve_login", "string", ["length" => 255, "null" => true])
            ->addColumn("givve_password", "string", ["length" => 255, "null" => true])
            ->save();

        $this->table('givve_voucher', ['id' => false, 'primary_key' => ["voucher_id", "employee_id"]])
            ->addColumn('voucher_id', 'string')
            ->addColumn('employee_id', 'integer')
            ->addColumn('balance', 'string')
            ->addColumn('updated', 'timestamp')
            ->addColumn('address_line_1', 'string')
            ->addColumn('address_line_2', 'string')
            ->save();

        $this->table('givve_voucher_transaction', ['id' => false, 'primary_key' => ["transaction_id", "voucher_id"]])
            ->addColumn('transaction_id', 'string')
            ->addColumn('voucher_id', 'string')
            ->addColumn('description', 'string')
            ->addColumn('booked', 'timestamp')
            ->addColumn('amount', 'string')
            ->save();
    }
}
