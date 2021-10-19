<?php


use Phinx\Migration\AbstractMigration;

class ChangeMessageColumnEmploymentAgreement extends AbstractMigration
{
    public function up()
    {
        $this->table('agreements')
            ->changeColumn('confirm_message', 'string', ['limit' => 500, "null" => true])
            ->save();
    }
    
    public function down()
    {
        $this->table('agreements')
        ->changeColumn('confirm_message', 'string', ['limit' => 500, "null" => true])
        ->save();
    }
}
