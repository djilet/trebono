<?php

use Phinx\Migration\AbstractMigration;

class CreditorOptionGlobal extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET level_global='Y' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__CREDITOR_BOOKING));
    }

    public function down()
    {
        $this->execute("UPDATE option SET level_global='N' WHERE code = ".Connection::GetSQLString(OPTION__TRAVEL__MAIN__CREDITOR_BOOKING));
    }
}
