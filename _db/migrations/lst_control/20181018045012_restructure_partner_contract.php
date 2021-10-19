<?php


use Phinx\Migration\AbstractMigration;

class RestructurePartnerContract extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE partner_contract ADD COLUMN start_date date");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN end_date date");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN company_unit_id integer");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN product_id integer");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN created timestamp without time zone");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN start_user_id integer");
        $this->execute("ALTER TABLE partner_contract ADD COLUMN end_user_id integer");
        $this->execute("UPDATE partner_contract 
          SET start_date=s.start_date, end_date=s.end_date, company_unit_id=s.company_unit_id, product_id=s.product_id, created=s.created, start_user_id=s.start_user_id, end_user_id=s.end_user_id
          FROM (SELECT start_date, end_date, company_unit_id, product_id, contract_id, created, start_user_id, end_user_id FROM company_unit_contract) s
          WHERE partner_contract.contract_id=s.contract_id
          ");
        $this->execute("DELETE FROM partner_contract WHERE company_unit_id IS NULL OR product_id IS NULL");
        $this->execute("ALTER TABLE	partner_contract ALTER COLUMN company_unit_id SET NOT NULL");
        $this->execute("ALTER TABLE	partner_contract ALTER COLUMN product_id SET NOT NULL");
        $this->execute("ALTER TABLE	partner_contract ALTER COLUMN created SET NOT NULL");
        $this->execute("ALTER TABLE partner_contract ALTER COLUMN contract_id DROP NOT NULL");
    }

    public function down(){
        $this->execute("UPDATE partner_contract 
          SET contract_id=s.contract_id
          FROM (SELECT start_date, end_date, company_unit_id, product_id, contract_id, created, start_user_id, end_user_id FROM company_unit_contract) s
          WHERE partner_contract.company_unit_id=s.company_unit_id AND partner_contract.product_id=s.product_id AND partner_contract.created=s.created
          ");
        $this->execute("ALTER TABLE partner_contract ALTER COLUMN contract_id SET NOT NULL");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN start_date");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN end_date");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN company_unit_id");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN product_id");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN created");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN start_user_id");
        $this->execute("ALTER TABLE partner_contract DROP COLUMN end_user_id");
    }
}
