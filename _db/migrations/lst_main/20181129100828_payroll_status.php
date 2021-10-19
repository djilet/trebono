<?php


use Phinx\Migration\AbstractMigration;

class PayrollStatus extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE payroll ADD COLUMN status character varying");
    }

    public function down(){
        $this->execute("ALTER TABLE payroll DROP COLUMN status");
    }
}
