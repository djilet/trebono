<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptRecreationOption extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "info-material-status", "Material status");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "info-material-status", "Familienstand");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "info-material-status", "Familienstand");

        $this->langVarList[] = new LangVar("en", "php", "product", "common", "info-сhild-сount", "Child count");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "info-сhild-сount", "Anzahl der Kinder");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "info-сhild-сount", "Anzahl der Kinder");
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
