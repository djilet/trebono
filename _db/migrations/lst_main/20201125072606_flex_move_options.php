<?php

use Phinx\Migration\AbstractMigration;

class FlexMoveOptions extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE option SET
                            sort_order='5',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_OPTION));

        $this->execute("UPDATE option SET
                            sort_order='6',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE));

        $this->execute("UPDATE option SET
                            sort_order='7',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE));

        $this->execute("UPDATE option SET
                            sort_order='8',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_FREE_UNITS));

        $this->execute("UPDATE option SET
                            sort_order='5',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION));

        $this->execute("UPDATE option SET
                            sort_order='6',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE));

        $this->execute("UPDATE option SET
                            sort_order='7',
                            group_id='1'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE));
    }

    public function down()
    {
        $this->execute("UPDATE option SET
                            sort_order='5',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_OPTION));

        $this->execute("UPDATE option SET
                            sort_order='6',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PRICE));

        $this->execute("UPDATE option SET
                            sort_order='7',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE));

        $this->execute("UPDATE option SET
                            sort_order='8',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__FOOD__MAIN__FLEX_FREE_UNITS));

        $this->execute("UPDATE option SET
                            sort_order='9',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PRICE));

        $this->execute("UPDATE option SET
                            sort_order='10',
                            group_id='3'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE));

        $this->execute("UPDATE option SET
                            sort_order='10',
                            group_id='7'
							WHERE code=".Connection::GetSQLString(OPTION__BENEFIT_VOUCHER__MAIN__AUTO_GENERATION));
    }
}
