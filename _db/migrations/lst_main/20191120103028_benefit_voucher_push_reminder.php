<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherPushReminder extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_benefit_voucher_expire_1", "Text of notification when benefit voucher expires in 1 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_benefit_voucher_expire_1", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_benefit_voucher_expire_1", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_benefit_voucher_expire_3", "Text of notification when benefit voucher expires in 3 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_benefit_voucher_expire_3", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_benefit_voucher_expire_3", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_benefit_voucher_expire_6", "Text of notification when benefit voucher expires in 6 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_benefit_voucher_expire_6", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 6 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_benefit_voucher_expire_6", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 6 Monat");
    }

    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('push_remind_benefit_voucher_expire_6', 'Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende Sachbezug Gutscheine offen: 
%voucher_list%Sie haben noch 6 Monate Zeit Belege zu fotografieren, ansonsten verfallen die Sachbezug Gutscheine leider. Viel Erfolg, Ihr trebono Team.','p_push', 'plain', ".$updated."),
                                            ('push_remind_benefit_voucher_expire_3', 'Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende Sachbezug Gutscheine offen: 
%voucher_list%Sie haben noch 3 Monate Zeit Belege zu fotografieren, ansonsten verfallen die Sachbezug Gutscheine leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.','p_push', 'plain', ".$updated."),
                                            ('push_remind_benefit_voucher_expire_1', 'Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende Sachbezug Gutscheine offen: 
%voucher_list%Sie haben nur noch diesen Monate Zeit Belege zu fotografieren, ansonsten verfallen die Sachbezug Gutscheine leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.','p_push', 'plain', ".$updated.")");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='push_remind_benefit_voucher_expire_1'");
        $this->execute("DELETE FROM config WHERE code='push_remind_benefit_voucher_expire_3'");
        $this->execute("DELETE FROM config WHERE code='push_remind_benefit_voucher_expire_6'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
