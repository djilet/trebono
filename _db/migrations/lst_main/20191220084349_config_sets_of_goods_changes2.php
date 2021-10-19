<?php

use Phinx\Migration\AbstractMigration;

class ConfigSetsOfGoodsChanges2 extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE voucher SET reason='alles für deine gesunde Ernährung' WHERE reason='alles für deine gute Ernährung'");

        $this->execute("UPDATE config SET value='alles für deine gesunde Ernährung
alles für deine Ernährung
alles für dein Auto
alles für deine Gesundheit
alles für dein Haus
alles für deine digitale Welt
alles für deinen Haushalt
alles für deine Mode' WHERE code='voucher_sets_of_goods'");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("UPDATE voucher_history SET value='alles für deine gesunde Ernährung' WHERE value='alles für deine gute Ernährung'");
    }

    public function down()
    {
        $this->execute("UPDATE voucher SET reason='alles für deine gute Ernährung' WHERE reason='alles für deine gesunde Ernährung'");

        $stmt = GetStatement(DB_CONTROL);
        $stmt->Execute("UPDATE voucher_history SET value='alles für deine gute Ernährung' WHERE value='alles für deine gesunde Ernährung'");

        $this->execute("UPDATE config SET value='alles für deine gute Ernährung
alles für deine Ernährung
alles für dein Auto
alles für deine Gesundheit
alles für dein Haus
alles für deine digitale Welt
alles für deinen Haushalt
alles für deine Mode' WHERE code='voucher_sets_of_goods'");
    }
}
