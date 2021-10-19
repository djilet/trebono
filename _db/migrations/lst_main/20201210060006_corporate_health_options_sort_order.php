<?php

use Phinx\Migration\AbstractMigration;

class CorporateHealthOptionsSortOrder extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET sort_order='1' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY));
        $this->execute("UPDATE option SET sort_order='2' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY));
        $this->execute("UPDATE option SET sort_order='3' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT));
        $this->execute("UPDATE option SET sort_order='4' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION));
    }

    public function down()
    {
        $this->execute("UPDATE option SET sort_order='1' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY));
        $this->execute("UPDATE option SET sort_order='1' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY));
        $this->execute("UPDATE option SET sort_order='2' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT));
        $this->execute("UPDATE option SET sort_order='3' WHERE code=".Connection::GetSQLString(OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION));
    }
}
