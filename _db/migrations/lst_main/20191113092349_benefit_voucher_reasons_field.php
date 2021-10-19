<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherReasonsField extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "voucher_edit.html", "SetOfGoods", "Category");
        $this->langVarList[] = new LangVar("de", "template", "company", "voucher_edit.html", "SetOfGoods", "Kategorie");
        $this->langVarList[] = new LangVar("tr", "template", "company", "voucher_edit.html", "SetOfGoods", "Kategorie");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "SetOfGoods", "Category");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "SetOfGoods", "Kategorie");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "SetOfGoods", "Kategorie");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-monthly-limit-exceeded", "Voucher amount greater than monthly limit for Benefit Voucher Service");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-monthly-limit-exceeded", "Gutscheinbetrag größer als Monatslimit für Sachbezug Gutschein");
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
