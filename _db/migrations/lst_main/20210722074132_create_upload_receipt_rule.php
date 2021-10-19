<?php

use Phinx\Migration\AbstractMigration;

class CreateUploadReceiptRule extends AbstractMigration
{
    private $langVarList = [];

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptInvalidFileFormat", "Upload support only jpg format!");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptInvalidFileFormat", "Upload-Unterstützung nur JPG-Format!");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptInvalidFileFormat", "Yalnızca jpg biçimini destekleyin!");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptEmptyFile", "Please fill file field!");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptEmptyFile", "Bitte Dateifeld ausfüllen!");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptEmptyFile", "Lütfen dosya alanını doldurunuz!");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptModalTitle", "Upload Receipt");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptModalTitle", "Beleg hochladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptModalTitle", "Fiş Yükle");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptService", "Service");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptService", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptService", "Hizmet");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptFile", "File");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptFile", "Datei");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptFile", "Dosya");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptClose", "Close");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptClose", "Schließen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptClose", "Kapat");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptSend", "Send");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptSend", "Senden");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptSend", "Gönderen");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "upload_receipt_rule_text", "I accept the agreement!");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "upload_receipt_rule_text", "Ich akzeptiere die Vereinbarung!");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "upload_receipt_rule_text", "Anlaşmayı kabul ediyorum!");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "UploadReceiptButton", "Upload Receipt");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "UploadReceiptButton", "Beleg hochladen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "UploadReceiptButton", "Fiş Yükle");
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
