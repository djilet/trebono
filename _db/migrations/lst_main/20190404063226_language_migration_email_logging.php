<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationEmailLogging extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "Email", "Email");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "Email", "Email");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "Email", "Email");

        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "Title", "Title");
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "Title", "Titel");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "Title", "Titel");

        $this->langVarList[] = new LangVar("en", "php", "core", "_config_navigation.html", "section-email", "Email logging");
        $this->langVarList[] = new LangVar("de", "php", "core", "_config_navigation.html", "section-email", "Email-Benachrichtigung");
        $this->langVarList[] = new LangVar("tr", "php", "core", "_config_navigation.html", "section-email", "Email-Benachrichtigung");
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
