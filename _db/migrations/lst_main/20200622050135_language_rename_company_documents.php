<?php

use Phinx\Migration\AbstractMigration;

class LanguageRenameCompanyDocuments extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CompanyDocumentRename", "Rename document");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CompanyDocumentRename", "Dokument umbenennen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CompanyDocumentRename", "Dokument umbenennen");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CompanyDocumentFillNewName", "Fill the new document name");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CompanyDocumentFillNewName", "Füllen Sie den neuen Dokumentnamen aus");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CompanyDocumentFillNewName", "Füllen Sie den neuen Dokumentnamen aus");
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
