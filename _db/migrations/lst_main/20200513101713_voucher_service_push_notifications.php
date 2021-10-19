<?php

use Phinx\Migration\AbstractMigration;

class VoucherServicePushNotifications extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_1", "Text of notification when voucher expires in 1 month");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_1", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_1", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");

        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_3", "Text of notification when voucher expires in 3 month");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_3", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_3", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");

        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_6", "Text of notification when voucher expires in 6 month");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_6", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 6 Monat");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_6", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 6 Monat");


        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_3_month", "Text of notification when voucher expires in 3 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_3_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_3_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 3 Monat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_2_month", "Text of notification when benefit voucher expires in 2 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_2_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 2 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_2_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 2 Monat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_1_month", "Text of notification when benefit voucher expires in 1 month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_1_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_1_month", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 1 Monat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_14_day", "Text of notification when benefit voucher expires in 14 days");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_14_day", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 14 Tage");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_14_day", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 14 Tage");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_remind_voucher_service_expire_7_day", "Text of notification when benefit voucher expires in 7 days");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_remind_voucher_service_expire_7_day", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 7 Tage");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_remind_voucher_service_expire_7_day", "Benachrichtigungstext bei Ablauf des Leistungsgutscheins in 7 Tage");
    }

    public function up()
    {
        $this->execute(
            "UPDATE config SET code='push_remind_voucher_service_expire_3_month', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
            %voucher_list%Sie haben noch 3 Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_6';
            
            UPDATE config SET code='push_remind_voucher_service_expire_2_month', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
            %voucher_list%Sie haben noch 2 Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_3';
            
            UPDATE config SET code='push_remind_voucher_service_expire_1_month', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
            %voucher_list%Sie haben nur noch diesen Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_1';
            
            INSERT INTO config (code, value, group_code, editor, updated, sort_order) VALUES (
                'push_remind_voucher_service_expire_14_day',
                'Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
                %voucher_list%Sie haben nur noch 14 Tage Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.',
                'p_push',
                'plain',
                ".Connection::GetSQLString(GetCurrentDateTime()).",
                0
            );
            
            INSERT INTO config (code, value, group_code, editor, updated, sort_order) VALUES (
                'push_remind_voucher_service_expire_7_day',
                'Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
                %voucher_list%Sie haben nur noch 7 Tage Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.',
                'p_push',
                'plain',
                ".Connection::GetSQLString(GetCurrentDateTime()).",
                0
            );"
        );

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute(
            "UPDATE config SET code='push_remind_voucher_service_expire_6', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
%voucher_list%Sie haben noch 6 Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_3_month';

            UPDATE config SET code='push_remind_voucher_service_expire_3', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
%voucher_list%Sie haben noch 3 Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_2_month';

            UPDATE config SET code='push_remind_voucher_service_expire_1', value='Hallo %salutation% %first_name% %last_name%, Sie haben noch folgende %product_name% offen: 
%voucher_list%Sie haben nur noch diesen Monate Zeit Belege zu fotografieren, ansonsten verfallen die %product_name% leider zum 31.12. diesen Jahres. Viel Erfolg, Ihr trebono Team.' WHERE code='push_remind_voucher_service_expire_1_month';

DELETE FROM config WHERE code IN ('push_remind_voucher_service_expire_14_day', 'push_remind_voucher_service_expire_7_day');"
        );

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
