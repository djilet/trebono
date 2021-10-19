<?php


use Phinx\Migration\AbstractMigration;

class AutodenyReason extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('receipt_autodeny_month_limit', 'monatliches Limit erreicht','r_autodeny', 'plain', ".$updated."),
                                            ('receipt_autodeny_year_limit', 'jahrliches Limit erreicht','r_autodeny', 'plain', ".$updated.")");
    }

    public function down(){
        $this->execute("DELETE FROM config WHERE group_code='r_autodeny'");
    }
}
