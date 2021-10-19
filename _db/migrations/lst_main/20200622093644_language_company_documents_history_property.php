<?php

use Phinx\Migration\AbstractMigration;

class LanguageCompanyDocumentsHistoryProperty extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-document-title", "File name");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-document-title", "Dateiname");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-document-title", "Dateiname");
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
