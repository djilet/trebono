<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptProductGroupOptions extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TotalAvailable", "On receipt date total available amount");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");

        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "On receipt date available vouchers");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");

        $this->delLangVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Approved at vouchers");
        $this->delLangVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");
        $this->delLangVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_receipt_options_table.html", "TotalAvailable", "On receipt date total available amount");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_receipt_options_table.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_receipt_options_table.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_receipt_options_table.html", "AvailableVouchers", "On receipt date available vouchers");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_receipt_options_table.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_receipt_options_table.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_receipt_options_table.html", "ApprovedAtVouchers", "Approved at vouchers");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_receipt_options_table.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_receipt_options_table.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");
    }

    public function up()
    {
        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }

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

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
