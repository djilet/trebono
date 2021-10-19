<?php

use Phinx\Migration\AbstractMigration;

class LanguageAcceptedDocuments extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "recreation-confirmations", "Recreation confirmations");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "recreation-confirmations", "Erholungsbestätigungen");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "recreation-confirmations", "Erholungsbestätigungen");
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
