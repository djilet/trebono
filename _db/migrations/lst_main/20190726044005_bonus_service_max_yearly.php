<?php


use Phinx\Migration\AbstractMigration;

class BonusServiceMaxYearly extends AbstractMigration
{
    public function up()
    {
        $query = "UPDATE option SET level_employee ='Y' WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__AMOUNT_PER_YEAR);
        $this->execute($query);
    }

    public function down()
    {
        $query = "UPDATE option SET level_employee ='N' WHERE code=".Connection::GetSQLString(OPTION__BONUS__MAIN__AMOUNT_PER_YEAR);
        $this->execute($query);
    }
}
