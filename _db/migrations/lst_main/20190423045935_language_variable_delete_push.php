<?php


use Phinx\Migration\AbstractMigration;

class LanguageVariableDeletePush extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "SendPushForFewNoCompanyConfirm", "No company unit chosen. Are you sure you want to send message for all companies?");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "SendPushForFewNoCompanyConfirm", "Keine Unternehmenseinheit ausgewählt. Sind Sie sicher, dass Sie eine Nachricht für alle Unternehmen senden möchten?");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "SendPushForFewNoCompanyConfirm", "Keine Unternehmenseinheit ausgewählt. Sind Sie sicher, dass Sie eine Nachricht für alle Unternehmen senden möchten?");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
