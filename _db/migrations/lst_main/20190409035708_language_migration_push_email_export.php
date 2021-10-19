<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationPushEmailExport extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "PushForFewExportEmail", "Export email addresses");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "PushForFewExportEmail", "E-Mail-Adressen exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "PushForFewExportEmail", "E-Mail-Adressen exportieren");

        $this->langVarList[] = new LangVar("en", "php", "core", "config_list.html", "start-date", "Start date");
        $this->langVarList[] = new LangVar("de", "php", "core", "config_list.html", "start-date", "Start-Datum");
        $this->langVarList[] = new LangVar("tr", "php", "core", "config_list.html", "start-date", "Start-Datum");

        $this->langVarList[] = new LangVar("en", "php", "core", "config_list.html", "end-date", "End date");
        $this->langVarList[] = new LangVar("de", "php", "core", "config_list.html", "end-date", "Ende-Datum");
        $this->langVarList[] = new LangVar("tr", "php", "core", "config_list.html", "end-date", "Ende-Datum");
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
