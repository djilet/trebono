<?php

use Phinx\Migration\AbstractMigration;

class LanguageBillableItems extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "block_billable_statistics.html", "AreYouSure", "Are you sure?");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_billable_statistics.html", "AreYouSure", "Bist du dir sicher?");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_billable_statistics.html", "AreYouSure", "Bist du dir sicher?");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_billable_statistics.html", "SuccessfullyDisabled", "Successfully disabled");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_billable_statistics.html", "SuccessfullyDisabled", "Erfolgreich deaktiviert");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_billable_statistics.html", "SuccessfullyDisabled", "Erfolgreich deaktiviert");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_billable_statistics.html", "SuccessfullyActivated", "Successfully activated");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_billable_statistics.html", "SuccessfullyActivated", "Erfolgreich aktiviert");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_billable_statistics.html", "SuccessfullyActivated", "Erfolgreich aktiviert");
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
