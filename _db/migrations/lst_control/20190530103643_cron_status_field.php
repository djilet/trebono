<?php


use Phinx\Migration\AbstractMigration;

class CronStatusField extends AbstractMigration
{
    public function up()
    {
        $this->table("operation_cron")
        ->addColumn("status", "text", ["null" => true])
        ->addColumn("status_updated", "datetime", ["null" => true])
        ->save();
    }
    
    public function down()
    {
        $this->table("operation_cron")
        ->removeColumn("status")
        ->removeColumn("status_updated")
        ->save();
    }
}
