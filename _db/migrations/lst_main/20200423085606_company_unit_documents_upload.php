<?php

use Phinx\Migration\AbstractMigration;

class CompanyUnitDocumentsUpload extends AbstractMigration
{

    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CompanyDocumentUploadFileEmpty", "File is empty");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CompanyDocumentUploadFileEmpty", "Datei ist leer");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CompanyDocumentUploadFileEmpty", "Datei ist leer");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "no-file-uploaded", "File is empty");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "no-file-uploaded", "Datei ist leer");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "no-file-uploaded", "Datei ist leer");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "error-file-upload", "Error occurred while file upload");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "error-file-upload", "Beim Hochladen der Datei ist ein Fehler aufgetreten");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "error-file-upload", "Beim Hochladen der Datei ist ein Fehler aufgetreten");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "contract-limit-reached", "You can only upload 10 contracts maximum");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "contract-limit-reached", "Sie können maximal 10 Verträge hochladen");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "contract-limit-reached", "Sie können maximal 10 Verträge hochladen");
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
