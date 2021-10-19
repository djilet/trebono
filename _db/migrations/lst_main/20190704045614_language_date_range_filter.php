<?php


use Phinx\Migration\AbstractMigration;

class LanguageDateRangeFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "push_history.html", "FilterDateRange", "Datum & Zeitbereich");
        $this->langVarList[] = new LangVar("en", "template", "core", "push_history.html", "FilterDateRange", "Date & Time Range");
        $this->langVarList[] = new LangVar("tr", "template", "core", "push_history.html", "FilterDateRange", "Datum & Zeitbereich");

        $this->langVarList[] = new LangVar("de", "template", "core", "logging_cron.html", "FilterDateRange", "Datum & Zeitbereich");
        $this->langVarList[] = new LangVar("en", "template", "core", "logging_cron.html", "FilterDateRange", "Date & Time Range");
        $this->langVarList[] = new LangVar("tr", "template", "core", "logging_cron.html", "FilterDateRange", "Datum & Zeitbereich");
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
