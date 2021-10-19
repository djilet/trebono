<?php

use Phinx\Migration\AbstractMigration;

class LanguageBookkeepingOperation extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-bookkeeping_export_list", "Exportkostenliste für Reisekosten anzeigen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-bookkeeping_export_list", "View travel cost export list");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-bookkeeping_export_list", "Exportkostenliste für Reisekosten anzeigen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-bookkeeping_export", "Reisekosten Export");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-bookkeeping_export", "Start export");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-bookkeeping_export", "Reisekosten Export");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-bookkeeping_export_id", "Export herunterladen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-bookkeeping_export_id", "Download export");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-bookkeeping_export_id", "Export herunterladen");
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
