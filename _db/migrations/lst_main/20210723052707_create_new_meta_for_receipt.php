<?php

use Phinx\Migration\AbstractMigration;

class CreateNewMetaForReceipt extends AbstractMigration
{
    public function up()
    {
        $this->table('receipt')
            ->addColumn('is_web_upload', 'smallinteger', ['null' => true])
            ->save();
    }

    public function down()
    {
        $this->table("receipt")
            ->removeColumn("is_web_upload")
            ->save();
    }
}
