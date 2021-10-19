<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmailChangingBankingDetails extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "notification-sent", "Notification of changing banking details sent");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "notification-sent", "Benachrichtigung über Änderung der Bankverbindung gesendet");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "notification-sent", "Benachrichtigung über Änderung der Bankverbindung gesendet");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
