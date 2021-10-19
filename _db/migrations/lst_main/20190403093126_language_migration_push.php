<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationPush extends AbstractMigration
{
    private $langVarList = array();
    private $langVarUpdateList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "PushForFewUserList", "View user list");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "PushForFewUserList", "Benutzerliste anzeigen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "PushForFewUserList", "Benutzerliste anzeigen");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendPushContactTypePlaceholder", "Contact type");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendPushContactTypePlaceholder", "Rolle");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendPushContactTypePlaceholder", "Rolle");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendPushFor", "Send push for ");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendPushFor", "Senden Sie Push für ");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendPushFor", "Senden Sie Push für ");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendEmailFor", "Send email for ");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendEmailFor", "Senden Sie E-Mail für ");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendEmailFor", "Senden Sie E-Mail für ");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendForUser", " user");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendForUser", " Nutzer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendForUser", " Nutzer");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendForUsers", " users");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendForUsers", " Benutzer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendForUsers", " Benutzer");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_user_list_for_push.html", "UserName", "Name");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_user_list_for_push.html", "UserName", "Name");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_user_list_for_push.html", "UserName", "Name");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_user_list_for_push.html", "CompanyUnitTitle", "Company unit title");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_user_list_for_push.html", "CompanyUnitTitle", "Unternehmensname");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_user_list_for_push.html", "CompanyUnitTitle", "Unternehmensname");

        $this->langVarUpdateList[] = new LangVar("en", "template", "core", "config_list.html", "SendPushContactPlaceholder", "Contact person");
        $this->langVarUpdateList[] = new LangVar("de", "template", "core", "config_list.html", "SendPushContactPlaceholder", "Kontaktpersonen");
        $this->langVarUpdateList[] = new LangVar("tr", "template", "core", "config_list.html", "SendPushContactPlaceholder", "Kontaktpersonen");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->langVarUpdateList as $langVar)
        {
            $query = $langVar->GetUpdateQuery();
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
