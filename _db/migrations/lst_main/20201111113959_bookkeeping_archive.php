<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class BookkeepingArchive extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-bookkeeping_export_delete", "Reisekostenexport zurücksetzen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-bookkeeping_export_delete", "Reset travel cost export");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-bookkeeping_export_delete", "Reisekostenexport zurücksetzen");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExportFor", "Travel cost export for");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExportFor", "Reisekosten Export für");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "BookkeepingExportFor", "Reisekosten Export für");

        $this->langVarList[] = new LangVar("en", "template", "billing", "bookkeeping_export_list.html", "RevisionHistory", "rev. history");
        $this->langVarList[] = new LangVar("de", "template", "billing", "bookkeeping_export_list.html", "RevisionHistory", "Historie");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "bookkeeping_export_list.html", "RevisionHistory", "Historie");
    }

    public function up()
    {
        $this->table("bookkeeping_export")
            ->addColumn("archive", Literal::from("flag"), ["null" => false, "default" => "N"])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("bookkeeping_export")
            ->removeColumn("archive")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
