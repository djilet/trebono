<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollGenerationOption extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "PayrollMonth", "Payroll month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "PayrollMonth", "Abrechnungsmonat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "PayrollMonth", "Abrechnungsmonat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "CurrentMonth", "current month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "CurrentMonth", "aktueller Monat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "CurrentMonth", "aktueller Monat");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "LastMonth", "last month");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "LastMonth", "Letzter Monat");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "LastMonth", "Letzter Monat");
    }

    public function up()
    {
        $this->table("company_unit")
            ->addColumn('payroll_month', 'string', ['default' => 'last_month'])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("company_unit")
            ->removeColumn('payroll_month')
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
