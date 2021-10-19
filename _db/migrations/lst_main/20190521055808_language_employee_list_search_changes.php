<?php


use Phinx\Migration\AbstractMigration;

class LanguageEmployeeListSearchChanges extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "OptionOperation", "Operation");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "OptionOperation", "Operation");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "OptionOperation", "Operation");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "available-receipt-value-month", "Available receipt value (current month)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "available-receipt-value-month", "Verfügbarer Belegwert (aktueller Monat)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "available-receipt-value-month", "Verfügbarer Belegwert (aktueller Monat)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "available-receipt-value-year", "Available receipt value (current year)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "available-receipt-value-year", "Verfügbarer Belegwert (aktuelles Jahr)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "available-receipt-value-year", "Verfügbarer Belegwert (aktuelles Jahr)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "approved-receipt-value-month", "Approved receipt value (current month)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "approved-receipt-value-month", "Genehmigter Belegwert (aktueller Monat)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "approved-receipt-value-month", "Genehmigter Belegwert (aktueller Monat)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "approved-receipt-value-year", "Approved receipt value (current year)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "approved-receipt-value-year", "Genehmigter Belegwert (aktuelles Jahr)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "approved-receipt-value-year", "Genehmigter Belegwert (aktuelles Jahr)");
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
