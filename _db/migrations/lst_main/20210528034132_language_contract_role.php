<?php

use Phinx\Migration\AbstractMigration;

class LanguageContractRole extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "permission-contract", "Contract");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-contract", "Vertrag");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-contract", "Vertrag");
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
