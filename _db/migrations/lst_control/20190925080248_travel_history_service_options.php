<?php

use Phinx\Migration\AbstractMigration;

class TravelHistoryServiceOptions extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, value, created, user_id, created_from)
						VALUES 
						(
						".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
						0, 
						".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__CREDITOR_BOOKING)).",
						'N',
						'2018-01-01 00:00:00',
						1,
						'admin');");

        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, value, created, user_id, created_from)
						VALUES 
						(
						".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
						0, 
						".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE)).",
						'Y',
						'2018-01-01 00:00:00',
						1,
						'admin');");

        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, value, created, user_id, created_from)
						VALUES 
						(
						".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
						0, 
						".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__HOURS_UNDER)).",
						'12',
						'2018-01-01 00:00:00',
						1,
						'admin');");

        $this->execute("INSERT INTO option_value_history (level, entity_id, option_id, value, created, user_id, created_from)
						VALUES 
						(
						".Connection::GetSQLString(OPTION_LEVEL_GLOBAL).",
						0, 
						".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__HOURS_OVER)).",
						'24',
						'2018-01-01 00:00:00',
						1,
						'admin');");
    }

    public function down()
    {
        $this->execute("DELETE FROM option_value_history 
						WHERE option_id=".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__CREDITOR_BOOKING))." 
							AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." 
							AND created='2018-01-01 00:00:00' 
							AND user_id=1");

        $this->execute("DELETE FROM option_value_history 
						WHERE option_id=".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE))." 
							AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." 
							AND created='2018-01-01 00:00:00' 
							AND user_id=1");

        $this->execute("DELETE FROM option_value_history 
						WHERE option_id=".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__HOURS_UNDER))." 
							AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." 
							AND created='2018-01-01 00:00:00' 
							AND user_id=1");

        $this->execute("DELETE FROM option_value_history 
						WHERE option_id=".intval(Option::GetOptionIDByCode(OPTION__TRAVEL__MAIN__HOURS_OVER))." 
							AND level=".Connection::GetSQLString(OPTION_LEVEL_GLOBAL)." 
							AND created='2018-01-01 00:00:00' 
							AND user_id=1");
    }
}
