<?php

use Phinx\Migration\AbstractMigration;

class LanguageAddBackToContactEdit extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "partner", "contact_edit.html", "Back", "Back");
        $this->langVarList[] = new LangVar("de", "template", "partner", "contact_edit.html", "Back", "Zurück");
        $this->langVarList[] = new LangVar("tr", "template", "partner", "contact_edit.html", "Back", "Geri");

        $this->langVarList[] = new LangVar("en", "template", "company", "contact_edit.html", "Back", "Back");
        $this->langVarList[] = new LangVar("de", "template", "company", "contact_edit.html", "Back", "Zurück");
        $this->langVarList[] = new LangVar("tr", "template", "company", "contact_edit.html", "Back", "Geri");
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