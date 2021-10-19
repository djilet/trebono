<?php

use Phinx\Migration\AbstractMigration;

class CronOperationCheckWorkers extends AbstractMigration
{
    public function up()
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM operation_cron WHERE description LIKE 'Started check workers%'";
        $operationList = $stmt->FetchList($query);
        foreach ($operationList as $operation)
        {
            $this->execute("UPDATE operation_cron SET error_message=".Connection::GetSQLString($operation["type"])."
                WHERE operation_id=".Connection::GetSQLString($operation["operation_id"]));
        }

        $this->execute("UPDATE operation_cron SET type='check_workers' WHERE description LIKE 'Started check workers%'");
    }

    public function down()
    {
    }
}
