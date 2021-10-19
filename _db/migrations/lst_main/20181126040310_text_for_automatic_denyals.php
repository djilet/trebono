<?php


use Phinx\Migration\AbstractMigration;

class TextForAutomaticDenyals extends AbstractMigration
{
    public function up(){
        $query = "INSERT INTO config (code, value, group_code, editor, updated)
                    VALUES('message_automatic_denyal_month', 'Hallo %salutation% %first_name% %last_name%, Sie haben Ihr monatliches Limit für den %service% Service erreicht. Bitte schicken Sie für den Monat %month% keine Belege mehr. Vielen Dank, Ihr FIN-easy Team.',
                           'p_push', 'plain', '".GetCurrentDateTime()."'), 
                           ('message_automatic_denyal_year', 'Hallo %salutation% %first_name% %last_name%, Sie haben Ihr jahrliches Limit für den %service% Service erreicht. Bitte schicken Sie für den Jahre %year% keine Belege mehr. Vielen Dank, Ihr FIN-easy Team.',
                           'p_push', 'plain', '".GetCurrentDateTime()."')";

        $this->execute($query);
    }

    public function down(){
        $query = "DELETE FROM config WHERE code IN ('message_automatic_denyal_month', 'message_automatic_denyal_year')";
        $this->execute($query);
    }
}
