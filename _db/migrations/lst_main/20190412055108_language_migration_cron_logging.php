<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationCronLogging extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "Date", "Date");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "Date", "Datum");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "Date", "Datum");

        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "Description", "Description");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "Description", "Beschreibung");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "Description", "Beschreibung");

        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "IsSuccessful", "Is successful");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "IsSuccessful", "Ist erfolgreich");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "IsSuccessful", "Ist erfolgreich");

        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "Error", "Error message");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "Error", "Fehlermeldung");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "Error", "Fehlermeldung");

        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "LoggingCron", "Batch jobs monitor");
        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "LoggingCron", "Stapeljobs überwachen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "LoggingCron", "Stapeljobs überwachen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-cron-logging", "Batch jobs monitor");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-cron-logging", "Stapeljobs überwachen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-cron-logging", "Stapeljobs überwachen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-logging-cron", "Batch jobs monitor");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-logging-cron", "Stapeljobs überwachen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-logging-cron", "Stapeljobs überwachen");
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
