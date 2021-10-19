<?php


use Phinx\Migration\AbstractMigration;

class FinishPartnerContractRestructure extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE partner_contract DROP COLUMN contract_id");
    }
    public function down(){
        //This migration cannot be rollback
    }
}
