<?php

use Phinx\Migration\AbstractMigration;

class LanguageVariableMassMailSubject extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "common", "Error", "Error");
        $this->langVarList[] = new LangVar("de", "template", "core", "common", "Error", "Fehler");
        $this->langVarList[] = new LangVar("tr", "template", "core", "common", "Error", "Fehler");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendEmailSubjectPlaceholder", "Email subject");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendEmailSubjectPlaceholder", "E-Mail Betreff");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendEmailSubjectPlaceholder", "E-Mail Betreff");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendEmailEmptySubject", "Email subject can't be empty");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendEmailEmptySubject", "Der Betreff der E-Mail darf nicht leer sein");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendEmailEmptySubject", "Der Betreff der E-Mail darf nicht leer sein");
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
