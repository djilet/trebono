<?php

use Phinx\Migration\AbstractMigration;

class LanguageDataExportReceiver extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "permission-stored_data", "Data export receiver");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-stored_data", "Datenexportempfänger");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-stored_data", "Datenexportempfänger");

        $this->langVarList[] = new LangVar("en", "template", "core", "contact_edit.html", "StoredData", "Stored data");
        $this->langVarList[] = new LangVar("de", "template", "core", "contact_edit.html", "StoredData", "Gespeicherte Daten");
        $this->langVarList[] = new LangVar("tr", "template", "core", "contact_edit.html", "StoredData", "Gespeicherte Daten");

        $this->langVarList[] = new LangVar("en", "template", "core", "contact_edit.html", "CompanyUnit", "Company unit admin");
        $this->langVarList[] = new LangVar("de", "template", "core", "contact_edit.html", "CompanyUnit", "Unternehmenseinheit admin");
        $this->langVarList[] = new LangVar("tr", "template", "core", "contact_edit.html", "CompanyUnit", "Unternehmenseinheit admin");

        $this->langVarList[] = new LangVar("en", "template", "core", "contact_edit.html", "Employee", "Employee admin");
        $this->langVarList[] = new LangVar("de", "template", "core", "contact_edit.html", "Employee", "Mitarbeiteradministrator");
        $this->langVarList[] = new LangVar("tr", "template", "core", "contact_edit.html", "Employee", "Mitarbeiteradministrator");
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
