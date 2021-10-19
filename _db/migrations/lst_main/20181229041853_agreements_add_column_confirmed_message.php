<?php


use Phinx\Migration\AbstractMigration;

class AgreementsAddColumnConfirmedMessage extends AbstractMigration
{

    public function up()
    {
        $this->table('agreements')
            ->addColumn('confirm_message', "string", ["null" => true])
            ->save();
    }

    public function down()
    {
        $this->table('agreements')
            ->removeColumn('confirm_message')
            ->save();
    }
}
