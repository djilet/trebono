<?php

use Phinx\Migration\AbstractMigration;

class LanguageConfirmationPdf extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "agreements", "mail_pdf.html", "FooterConfirmText", "Digital bestätigt von");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "mail_pdf.html", "FooterConfirmText", "Digital bestätigt von");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "mail_pdf.html", "FooterConfirmText", "Digital bestätigt von");
        $this->langVarList[] = new LangVar("en", "template", "agreements", "mail_pdf.html", "FooterConfirmAm", "am");
        $this->langVarList[] = new LangVar("de", "template", "agreements", "mail_pdf.html", "FooterConfirmAm", "am");
        $this->langVarList[] = new LangVar("tr", "template", "agreements", "mail_pdf.html", "FooterConfirmAm", "am");
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
