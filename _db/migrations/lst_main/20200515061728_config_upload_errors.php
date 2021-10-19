<?php

use Phinx\Migration\AbstractMigration;

class ConfigUploadErrors extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "no-file-uploaded", "File is empty");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "no-file-uploaded", "Datei ist leer");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "no-file-uploaded", "Datei ist leer");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "error-file-upload", "Error occurred while file upload");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "error-file-upload", "Beim Hochladen der Datei ist ein Fehler aufgetreten");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "error-file-upload", "Beim Hochladen der Datei ist ein Fehler aufgetreten");
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
