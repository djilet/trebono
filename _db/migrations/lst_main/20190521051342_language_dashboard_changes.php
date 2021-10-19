<?php


use Phinx\Migration\AbstractMigration;

class LanguageDashboardChanges extends AbstractMigration
{
    private $langVarList = array();
    private $oldLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "CompanyUnitAdded", "Company units created / Total count of active company units");
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "CompanyUnitAdded", "Anzahl Kunden, die in obigem Zeitraum neu angelegt wurden /<br> Gesamtzahl der aktiven Kunden");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "CompanyUnitAdded", "Anzahl Kunden, die in obigem Zeitraum neu angelegt wurden /<br> Gesamtzahl der aktiven Kunden");

        $this->oldLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "CompanyUnitAdded", "Company units created");
        $this->oldLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "CompanyUnitAdded", "Anzahl Kunden, die in obigem Zeitraum neu angelegt wurden");
        $this->oldLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "CompanyUnitAdded", "Anzahl Kunden, die in obigem Zeitraum neu angelegt wurden");

        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Company units deactivated / Total count of inactive company units");
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Anzahl Kunden, die in obigem Zeitraum deaktiviert wurden /<br> Gesamtzahl inaktiver Kunden");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Anzahl Kunden, die in obigem Zeitraum deaktiviert wurden /<br> Gesamtzahl inaktiver Kunden");

        $this->oldLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Company units deactivated");
        $this->oldLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Anzahl Kunden, die in obigem Zeitraum deaktiviert wurden");
        $this->oldLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "CompanyUnitDeactivated", "Anzahl Kunden, die in obigem Zeitraum deaktiviert wurden");

        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "EmployeeAdded", "Employees created / Total count of active employees");
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "EmployeeAdded", "Anzahl Mitarbeiter, die in obigem Zeitraum neu angelegt wurden /<br> Gesamtzahl der aktiven Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "EmployeeAdded", "Anzahl Mitarbeiter, die in obigem Zeitraum neu angelegt wurden /<br> Gesamtzahl der aktiven Mitarbeiter");

        $this->oldLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "EmployeeAdded", "Employees created");
        $this->oldLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "EmployeeAdded", "Anzahl Mitarbeiter, die in obigem Zeitraum neu angelegt wurden");
        $this->oldLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "EmployeeAdded", "Anzahl Mitarbeiter, die in obigem Zeitraum neu angelegt wurden");

        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "EmployeeDeactivated", "Employees deactivated / Total count of inactive employees");
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "EmployeeDeactivated", "Anzahl Mitarbeiter, die in obigem Zeitraum deaktiviert wurden /<br> Gesamtzahl der inaktiven Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "EmployeeDeactivated", "Anzahl Mitarbeiter, die in obigem Zeitraum deaktiviert wurden /<br> Gesamtzahl der inaktiven Mitarbeiter");

        $this->oldLangVarList[] = new LangVar("en", "template", "core", "dashboard.html", "EmployeeDeactivated", "Employees deactivated");
        $this->oldLangVarList[] = new LangVar("de", "template", "core", "dashboard.html", "EmployeeDeactivated", "Anzahl Mitarbeiter, die in obigem Zeitraum deaktiviert wurden");
        $this->oldLangVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "EmployeeDeactivated", "Anzahl Mitarbeiter, die in obigem Zeitraum deaktiviert wurden");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetUpdateQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        foreach($this->oldLangVarList as $langVar)
        {
            $query = $langVar->GetUpdateQuery();
            $this->execute($query);
        }
    }
}
