<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptDownloadPdf extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList = [
            new LangVar("en", "template", "receipt", "receipt_edit.html", "DownloadPDF", "Download PDF"),
            new LangVar("de", "template", "receipt", "receipt_edit.html", "DownloadPDF", "Download PDF"),
            new LangVar("tr", "template", "receipt", "receipt_edit.html", "DownloadPDF", "Download PDF"),

            new LangVar("en", "template", "receipt", "receipt_edit.html", "ConfirmationPDF", "Confirmation PDF"),
            new LangVar("de", "template", "receipt", "receipt_edit.html", "ConfirmationPDF", "Confirmation PDF"),
            new LangVar("tr", "template", "receipt", "receipt_edit.html", "ConfirmationPDF", "Confirmation PDF"),
        ];
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
