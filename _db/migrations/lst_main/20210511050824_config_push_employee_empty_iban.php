<?php

use Phinx\Migration\AbstractMigration;

class ConfigPushEmployeeEmptyIban extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config-push_empty_iban", "Text der leeren IBAN-Benachrichtigung");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config-push_empty_iban", "Text of empty IBAN notification");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config-push_empty_iban", "Text der leeren IBAN-Benachrichtigung");
    }

    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute(
            "INSERT INTO config (code, value, group_code, editor, updated)
            VALUES (
            'push_empty_iban', 
            'ERINNERUNG: Hallo %last_name%, Ihre Bankdaten fehlen noch! Bitte im Web-Portal service.trebono.de eingeben. Wir können Ihnen sonst die eingelösten Gutscheinbeträge nicht erstatten. Vielen Dank, Ihr trebono Team',
            'p_push', 
            'plain', 
            ".$updated.")");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM config WHERE code='push_empty_iban'");

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
