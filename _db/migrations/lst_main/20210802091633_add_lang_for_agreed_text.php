<?php

use Phinx\Migration\AbstractMigration;

class AddLangForAgreedText extends AbstractMigration
{
    private $langVars = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_property_history.html", "AgreedText", "Agreed Text");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_property_history.html", "AgreedText", "Vereinbarter Text");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_property_history.html", "AgreedText", "Mutabık kalınan metin");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_property_history.html", "AgreedTextToolTip", "Text");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_property_history.html", "AgreedTextToolTip", "Text");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_property_history.html", "AgreedTextToolTip", "Metin");
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
