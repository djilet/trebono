<?php


use Phinx\Migration\AbstractMigration;

class CronLoggingType extends AbstractMigration
{
    public function up()
    {
        $this->table("operation_cron")
            ->addColumn("type", "string", ["null" => true])
            ->save();

        $this->execute("UPDATE operation_cron SET type='invoice_create' WHERE description LIKE 'Started creating invoices%'");
        $this->execute("UPDATE operation_cron SET type='payroll_create' WHERE description LIKE 'Started creating payrolls for%'");
        $this->execute("UPDATE operation_cron SET type='employee_deactivate' WHERE description LIKE 'Started deactivating employees%'");
        $this->execute("UPDATE operation_cron SET type='voucher' WHERE description LIKE 'Started generating and sending out vouchers%'");
        $this->execute("UPDATE operation_cron SET type='receipt_clean' WHERE description LIKE 'Started removing receipts without images%'");
        $this->execute("UPDATE operation_cron SET type='push_notification' WHERE description LIKE 'Started sending out reminder pushes%'");
    }

    public function down()
    {
        $this->table("operation_cron")
            ->removeColumn("type")
            ->save();
    }
}
