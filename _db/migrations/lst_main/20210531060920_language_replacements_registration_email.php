<?php

use Phinx\Migration\AbstractMigration;

class LanguageReplacementsRegistrationEmail extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "php", "core", "common", "replacement-base_url", "Base url"),
            new LangVar("de", "php", "core", "common", "replacement-base_url", "Basis-url"),
            new LangVar("tr", "php", "core", "common", "replacement-base_url", "Basis-url"),

            new LangVar("en", "php", "core", "common", "replacement-company-reg_email_text", "Individual text from company"),
            new LangVar("de", "php", "core", "common", "replacement-company-reg_email_text", "Individueller Text vom Unternehmen"),
            new LangVar("tr", "php", "core", "common", "replacement-company-reg_email_text", "Individueller Text vom Unternehmen"),

            new LangVar("en", "php", "core", "common", "replacement-email", "Email"),
            new LangVar("de", "php", "core", "common", "replacement-email", "Email"),
            new LangVar("tr", "php", "core", "common", "replacement-email", "Email"),

            new LangVar("en", "php", "core", "common", "replacement-password", "Password"),
            new LangVar("de", "php", "core", "common", "replacement-password", "Passwort"),
            new LangVar("tr", "php", "core", "common", "replacement-password", "Passwort"),

            new LangVar("en", "template", "core", "config_edit.html", "AvailableVariables", "Available variables"),
            new LangVar("de", "template", "core", "config_edit.html", "AvailableVariables", "Verfügbare Variablen"),
            new LangVar("tr", "template", "core", "config_edit.html", "AvailableVariables", "Verfügbare Variablen"),

            new LangVar("en", "template", "core", "config_edit.html", "AvailableVariablesComment", "(when clicked, inserting a variable at the cursor position)"),
            new LangVar("de", "template", "core", "config_edit.html", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)"),
            new LangVar("tr", "template", "core", "config_edit.html", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)"),
        ];
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
