<?php

use Phinx\Migration\AbstractMigration;

class LanguagePasswordReset extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "no-more-login-attempts-description", "Nachricht, die erklärt, dass alle Anmeldeversuche verwendet wurden");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "no-more-login-attempts-description", "Message explaining that all login attempts were used");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "no-more-login-attempts-description", "Nachricht, die erklärt, dass alle Anmeldeversuche verwendet wurden");
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
