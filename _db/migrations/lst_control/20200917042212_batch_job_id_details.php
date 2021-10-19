<?php

use Phinx\Migration\AbstractMigration;

class BatchJobIdDetails extends AbstractMigration
{
    public function change()
    {
        $this->table("operation_cron")
            ->addColumn("used_ids", "text", ["null" => true])
            ->save();
    }
}
