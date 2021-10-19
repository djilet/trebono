<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherReceiptsInVoucherEdit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ReceiptReceiptID", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ReceiptReceiptID", "Beleg ID");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ReceiptReceiptID", "Beleg ID");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ReceiptUpdated", "Last Update");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ReceiptUpdated", "Letze Aktualisierung");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ReceiptUpdated", "Letze Aktualisierung");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ReceiptCreated", "Created");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ReceiptCreated", "Erstellt am");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ReceiptCreated", "Erstellt am");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "Service", "Service");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "Service", "Modul");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "Service", "Modul");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ReceiptStatus", "Status");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ReceiptStatus", "Aktueller Status");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ReceiptStatus", "Aktueller Status");

        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "ApprovedAmount", "Approved amount");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "ApprovedAmount", "Genehmigter Betrag");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "ApprovedAmount", "Genehmigter Betrag");
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
