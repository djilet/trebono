<?php

use Phinx\Migration\AbstractMigration;

class LanguagePayrollReset extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Payroll", "Payroll");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Payroll", "Lohnliste");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Payroll", "Lohnliste");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "PayrollReset", "Payroll reset");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "PayrollReset", "Gehaltsabrechnung zurückgesetzt");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "PayrollReset", "Gehaltsabrechnung zurückgesetzt");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "PayrollDate", "Payroll date");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "PayrollDate", "Abrechnungsdatum");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "PayrollDate", "Abrechnungsdatum");

        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "PayrollAccessNote", "Save company/department to access payrolls");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "PayrollAccessNote", "Speichern Sie das Unternehmen / die Abteilung, um auf die Gehaltsabrechnung zuzugreifen");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "PayrollAccessNote", "Speichern Sie das Unternehmen / die Abteilung, um auf die Gehaltsabrechnung zuzugreifen");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "payroll-does-not-exist", "Payroll for this period doesn't exist");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "payroll-does-not-exist", "Die Abrechnung für diesen Zeitraum existiert nicht");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "payroll-does-not-exist", "Die Abrechnung für diesen Zeitraum existiert nicht");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "payroll-reset-success", "Payroll reset was successful");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "payroll-reset-success", "Gehaltsabrechnung zurückgesetzt war erfolgreich");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "payroll-reset-success", "Gehaltsabrechnung zurückgesetzt war erfolgreich");

        $this->langVarList[] = new LangVar("en", "php", "company", "common", "payroll-reset-fail", "Payroll reset was a failure");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "payroll-reset-fail", "Gehaltsabrechnung zurückgesetzt war ein Fehler");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "payroll-reset-fail", "Gehaltsabrechnung zurückgesetzt war ein Fehler");
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
