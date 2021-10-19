<?php


use Phinx\Migration\AbstractMigration;

class EmployeeGivveVoucherTransaction extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
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

    public function down()
    {
        $this->table("givve_voucher")->drop()->save();
        $this->table("givve_voucher_transaction")->drop()->save();
    }
}
