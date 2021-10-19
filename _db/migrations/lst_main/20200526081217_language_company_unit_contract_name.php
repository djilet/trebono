<?php

use Phinx\Migration\AbstractMigration;

class LanguageCompanyUnitContractName extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CompanyDocumentFillName", "Fill the document name");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CompanyDocumentFillName", "Füllen Sie den Dokumentnamen aus");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CompanyDocumentFillName", "Füllen Sie den Dokumentnamen aus");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CompanyDocumentUpdate", "Upload new document");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CompanyDocumentUpdate", "Neues Dokument hochladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CompanyDocumentUpdate", "Neues Dokument hochladen");

        $this->langVarList[] = new LangVar("en", "template", "company", "block_property_history.html", "Property", "Property");
        $this->langVarList[] = new LangVar("de", "template", "company", "block_property_history.html", "Property", "Eigentum");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_property_history.html", "Property", "Eigentum");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contract-empty-name", "File name is empty");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contract-empty-name", "Der Dateiname ist leer");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contract-empty-name", "Der Dateiname ist leer");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "modal-input-fill", "Fill the input");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "modal-input-fill", "Füllen Sie die Eingabe");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "modal-input-fill", "Füllen Sie die Eingabe");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-document-archive", "Archive");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-document-archive", "Archiv");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-document-archive", "Archiv");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "company-unit-document-value", "File");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "company-unit-document-value", "Datei");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "company-unit-document-value", "Datei");
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
