<?php

use Phinx\Migration\AbstractMigration;

class LanguageMaterialStatusPhp extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "material-status-single", "Single");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "material-status-singlematerial-status-single", "Single");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "material-status-single", "Single");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "material-status-married", "Married");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "material-status-married", "Verheiratet");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "material-status-married", "Verheiratet");
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
