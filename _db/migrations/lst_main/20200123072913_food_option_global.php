<?php

use Phinx\Migration\AbstractMigration;

class FoodOptionGlobal extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET level_global='Y' WHERE code = ".Connection::GetSQLString(OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY));
        $this->execute("UPDATE option SET level_global='Y' WHERE code = ".Connection::GetSQLString(OPTION__FOOD__MAIN__AUTO_ADOPTION));
    }

    public function down()
    {
        $this->execute("UPDATE option SET level_global='N' WHERE code = ".Connection::GetSQLString(OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY));
        $this->execute("UPDATE option SET level_global='N' WHERE code = ".Connection::GetSQLString(OPTION__FOOD__MAIN__AUTO_ADOPTION));
    }
}
