<?php

use Phinx\Migration\AbstractMigration;

class LanguageReplacementsMobileFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-mobile_model", "Mobiles modell");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-mobile_model", "Mobile model");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-mobile_model", "Mobiles modell");

        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-mobile_number", "Handynummer");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-mobile_number", "Mobile number");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-mobile_number", "Handynummer");
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
