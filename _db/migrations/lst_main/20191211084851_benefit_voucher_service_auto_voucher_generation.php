<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherServiceAutoVoucherGeneration extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "GenerateBenefitVouchersDate", "Date for generation vouchers");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "GenerateBenefitVouchersDate", "Datum für die Generierung von Gutscheinen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "GenerateBenefitVouchersDate", "Datum für die Generierung von Gutscheinen");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "GenerateBenefitVouchers", "Generate benefit vouchers");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "GenerateBenefitVouchers", "Leistungsgutscheine generieren");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "GenerateBenefitVouchers", "Leistungsgutscheine generieren");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "GenerateBenefitVouchersEmptyDate", "Please, select date for voucher generation");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "GenerateBenefitVouchersEmptyDate", "Bitte wählen Sie das Datum für die Erstellung des Gutscheins");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "GenerateBenefitVouchersEmptyDate", "Bitte wählen Sie das Datum für die Erstellung des Gutscheins");
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
