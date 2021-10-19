<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherFreequency extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "VoucherRecurrenceFrequency", "Reccuring frequency");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "VoucherRecurrenceFrequency", "Dauer-Gutschein Intervall");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "VoucherRecurrenceFrequency", "Dauer-Gutschein Intervall");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-frequency-monthly", "Monthly");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-frequency-monthly", "Monatlich");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-frequency-monthly", "Monatlich");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-frequency-quarterly", "Quarterly");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-frequency-quarterly", "Quartal");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-frequency-quarterly", "Quartal");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "voucher-frequency-yearly", "Yearly");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "voucher-frequency-yearly", "jährlich");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "voucher-frequency-yearly", "jährlich");
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
