<?php


use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CronLogging extends AbstractMigration
{
    public function up()
    {
        $this->table("operation_cron", ["id" => "operation_id"])
            ->addColumn("date", "timestamp", ["null" => false])
            ->addColumn("description", "text", ["null" => true])
            ->addColumn("is_successful", Literal::from("flag"), ["null" => false])
            ->addColumn("error_message", "text", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->dropTable("operation_cron");
    }
}
