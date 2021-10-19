<?php


use Phinx\Migration\AbstractMigration;

class DropNotNullOnCompanyUnitValue extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE company_unit_option_value ALTER COLUMN value DROP NOT NULL");
    }

    public function down(){
        //Cannot be rollback
    }
}
