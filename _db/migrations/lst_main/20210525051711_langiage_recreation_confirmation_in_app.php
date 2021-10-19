<?php

use Phinx\Migration\AbstractMigration;

class LangiageRecreationConfirmationInApp extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "api-recreation-confirmation-popup-description", "Nachricht im Freizeitdienst mit der Bitte um Bestätigung der Empfangsgenehmigung");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "api-recreation-confirmation-popup-description", "Message in Recreation Service, asking for confirmation of receipt approval");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "api-recreation-confirmation-popup-description", "Nachricht im Freizeitdienst mit der Bitte um Bestätigung der Empfangsgenehmigung");

        $this->langVarList[] = new LangVar("de", "php", "product", "common", "api-recreation-confirmation-popup", "Möchten Sie es wirklich beantragen?");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "api-recreation-confirmation-popup", "Are you sure you want to approve it?");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "api-recreation-confirmation-popup", "Möchten Sie es wirklich beantragen?");
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
