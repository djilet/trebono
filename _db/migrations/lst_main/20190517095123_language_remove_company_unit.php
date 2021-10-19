<?php


use Phinx\Migration\AbstractMigration;

class LanguageRemoveCompanyUnit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "remove-company-unit-id", "ID");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "remove-company-unit-id", "ID");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "remove-company-unit-id", "ID");
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
