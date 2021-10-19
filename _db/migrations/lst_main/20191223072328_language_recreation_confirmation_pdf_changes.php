<?php

use Phinx\Migration\AbstractMigration;

class LanguageRecreationConfirmationPdfChanges extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "agreements", "mail_pdf.html", "ConfirmationOfVacation", "Bestätigung Erholungsurlaub");
        $this->langVarList[] = new LangVar("en", "template", "agreements", "mail_pdf.html", "ConfirmationOfVacation", "Bestätigung Erholungsurlaub");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "mail_pdf.html", "ConfirmationOfVacation", "Bestätigung Erholungsurlaub");
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
