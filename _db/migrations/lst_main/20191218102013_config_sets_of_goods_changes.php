<?php

use Phinx\Migration\AbstractMigration;

class ConfigSetsOfGoodsChanges extends AbstractMigration
{

    public function up()
    {
        $this->execute("UPDATE voucher SET reason='alles für deine gute Ernährung' WHERE reason='alles für deine Ernährung' OR reason='Allgemeine Waren und Dienstleistungen'");
        $this->execute("UPDATE voucher SET reason='alles für deine Ernährung' WHERE reason='Ernährung Waren und Dienstleistungen'");
        $this->execute("UPDATE voucher SET reason='alles für dein Auto' WHERE reason='Auto Waren und Dienstleistungen' OR reason='Car related goods'");
        $this->execute("UPDATE voucher SET reason='alles für deine Gesundheit' WHERE reason='Gesundheit Waren und Dienstleistungen' OR reason='Health Goods'");
        $this->execute("UPDATE voucher SET reason='alles für deinen Haushalt' WHERE reason='Haushalt Waren und Dienstleistungen'");
        $this->execute("UPDATE voucher SET reason='alles für dein Haus' WHERE reason='Elektro Waren und Dienstleistungen'");

        $this->execute("UPDATE receipt SET sets_of_goods='alles für deine Ernährung' WHERE sets_of_goods='Ernährung War' OR sets_of_goods='Ernährung Waren und Dienstleistungen' OR sets_of_goods='Allgemeine Waren und Dienstleistungen' OR sets_of_goods='fvhgiuyvhbkhb' OR sets_of_goods=''");
        $this->execute("UPDATE receipt SET sets_of_goods='alles für dein Auto' WHERE sets_of_goods='Auto' OR sets_of_goods='Auto Waren und Dienstleistungen' OR sets_of_goods='Car related goods'");
        $this->execute("UPDATE receipt SET sets_of_goods='alles für deine Gesundheit' WHERE sets_of_goods='Gesundheit Waren und Dienstleistungen' OR sets_of_goods='Health Goods'");
        $this->execute("UPDATE receipt SET sets_of_goods='alles für dein Haus' WHERE sets_of_goods='Elektro Waren und Dienstleistungen'");
        $this->execute("UPDATE receipt SET sets_of_goods='alles für deinen Haushalt' WHERE sets_of_goods='Haushalt Waren und Dienstleistungen'");

        $this->execute("UPDATE config SET value='alles für deine gute Ernährung
alles für deine Ernährung
alles für dein Auto
alles für deine Gesundheit
alles für dein Haus
alles für deine digitale Welt
alles für deinen Haushalt
alles für deine Mode' WHERE code='voucher_sets_of_goods'");

        $stmt = GetStatement(DB_CONTROL);

        $stmt->Execute("UPDATE receipt_history SET value='alles für deine Ernährung' WHERE value='Ernährung War' OR value='Ernährung Waren und Dienstleistungen' OR value='Allgemeine Waren und Dienstleistungen' OR value='fvhgiuyvhbkhb' OR value=''");
        $stmt->Execute("UPDATE receipt_history SET value='alles für dein Auto' WHERE value='Auto' OR value='Auto Waren und Dienstleistungen' OR value='Car related goods'");
        $stmt->Execute("UPDATE receipt_history SET value='alles für deine Gesundheit' WHERE value='Gesundheit Waren und Dienstleistungen' OR value='Health Goods'");
        $stmt->Execute("UPDATE receipt_history SET value='alles für dein Haus' WHERE value='Elektro Waren und Dienstleistungen'");
        $stmt->Execute("UPDATE receipt_history SET value='alles für deinen Haushalt' WHERE value='Haushalt Waren und Dienstleistungen'");

        $stmt->Execute("UPDATE voucher_history SET value='alles für deine gute Ernährung' WHERE value='alles für deine Ernährung' OR value='Allgemeine Waren und Dienstleistungen'");
        $stmt->Execute("UPDATE voucher_history SET value='alles für deine Ernährung' WHERE value='Ernährung Waren und Dienstleistungen'");
        $stmt->Execute("UPDATE voucher_history SET value='alles für dein Auto' WHERE value='Auto Waren und Dienstleistungen' OR value='Car related goods'");
        $stmt->Execute("UPDATE voucher_history SET value='alles für deine Gesundheit' WHERE value='Gesundheit Waren und Dienstleistungen' OR value='Health Goods'");
        $stmt->Execute("UPDATE voucher_history SET value='alles für deinen Haushalt' WHERE value='Haushalt Waren und Dienstleistungen'");
        $stmt->Execute("UPDATE voucher_history SET value='alles für dein Haus' WHERE value='Elektro Waren und Dienstleistungen'");
    }

    public function down()
    {
        $this->execute("UPDATE voucher SET reason='alles für deine Ernährung' WHERE reason='alles für deine gute Ernährung'");
        $this->execute("UPDATE receipt SET sets_of_goods='alles für deine Ernährung' WHERE sets_of_goods='alles für deine gute Ernährung'");

        $stmt = GetStatement(DB_CONTROL);

        $stmt->Execute("UPDATE voucher_history SET value='alles für deine Ernährung' WHERE value='alles für deine gute Ernährung'");
        $stmt->Execute("UPDATE receipt_history SET value='alles für deine Ernährung' WHERE value='alles für deine gute Ernährung'");

        $this->execute("UPDATE config SET value='alles für deine Ernährung
alles für dein Auto
alles für deine Gesundheit
alles für dein Haus
alles für deine digitale Welt
alles für deinen Haushalt
alles für deine Mode' WHERE code='voucher_sets_of_goods'");
    }
}