<?php

use Phinx\Migration\AbstractMigration;

class DeactivationCronTypeRename extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE operation_cron SET type='deactivate' WHERE type='employee_deactivate'");
    }

    public function down()
    {
        $this->execute("UPDATE operation_cron SET type='employee_deactivate' WHERE type='deactivate'");
    }
}
