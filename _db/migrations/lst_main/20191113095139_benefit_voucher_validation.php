<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherValidation extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "receipt-benefit-voucher-limit-exceeded", "Not enough vouchers to cover receipt");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "receipt-benefit-voucher-limit-exceeded", "Nicht genügend Gutscheine für den Beleg");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "receipt-benefit-voucher-limit-exceeded", "Nicht genügend Gutscheine für den Beleg");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "permission-employee_view", "Employee");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "permission-employee_view", "Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "permission-employee_view", "Mitarbeiter");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TotalAvailable", "On receipt date total available amount");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TotalAvailable", "Am Eingangstermin insgesamt verfügbarer Betrag");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "On receipt date available vouchers");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "AvailableVouchers", "Am Empfangsdatum verfügbare Gutscheine");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Approved at vouchers");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "ApprovedAtVouchers", "Anerkannt bei Gutscheinen");
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
