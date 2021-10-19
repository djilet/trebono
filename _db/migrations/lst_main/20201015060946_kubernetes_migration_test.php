<?php

use Phinx\Migration\AbstractMigration;

class KubernetesMigrationTest extends AbstractMigration
{
    public function change()
    {
        #just a test to see if migrations work in k8
        $this->query("SELECT * FROM currency");
    }
}
