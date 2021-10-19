<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationPushVariables extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "AvailableVariables", "Available variables");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "AvailableVariables", "Verfügbare Variablen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "AvailableVariables", "Verfügbare Variablen");

        $this->langVarList[] = new LangVar("en", "template", "core", "config_list.html", "AvailableVariablesComment", "(when clicked, inserting a variable at the cursor position)");
        $this->langVarList[] = new LangVar("de", "template", "core", "config_list.html", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)");
        $this->langVarList[] = new LangVar("tr", "template", "core", "config_list.html", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "AvailableVariables", "Available variables");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "AvailableVariables", "Verfügbare Variablen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "AvailableVariables", "Verfügbare Variablen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "AvailableVariablesComment", "(when clicked, inserting a variable at the cursor position)");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "AvailableVariablesComment", "(Wenn Sie darauf klicken, fügen Sie eine Variable an der Cursorposition ein.)");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-salutation", "Salutation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-title", "Company Name");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-street", "Company Street");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-house", "Company House");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-zip-code", "Company Zip Code");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-city", "Company City");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-first-name", "First Name");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-first-name", "Vorname");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-first-name", "Vorname");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-last-name", "Last Name");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-last-name", "Nachname");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-last-name", "Nachname");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-birthday", "Birthdate");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-street", "Street");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-house", "House");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-zip-code", "Zip Code");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-zip-code", "Postleitzahl");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-zip-code", "Postleitzahl");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-city", "City");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "replacement-work-place", "Place Of Work");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "replacement-work-place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "replacement-work-place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-salutation", "Salutation");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-title", "Company Name");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-street", "Company Street");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-house", "Company House");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-zip-code", "Company Zip Code");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-city", "Company City");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-first-name", "First Name");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-first-name", "Vorname");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-first-name", "Vorname");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-last-name", "Last Name");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-last-name", "Nachname");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-last-name", "Nachname");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-birthday", "Birthdate");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-street", "Street");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-house", "House");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-zip-code", "Zip Code");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-zip-code", "Postleitzahl");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-zip-code", "Postleitzahl");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-city", "City");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-work-place", "Place Of Work");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-work-place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-work-place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-start-date", "Start Date Service");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-start-date", "Start Datum Sachlohnart");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-start-date", "Start Datum Sachlohnart");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-amount-per-month", "Monthly € Amount");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-amount-per-month", "Monatl. € Betrag");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-amount-per-month", "Monatl. € Betrag");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-units-per-month", "Units Per Month");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-units-per-month", "Max. Einheiten pro Monat");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-units-per-month", "Max. Einheiten pro Monat");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-meal-value", "Meal Value");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-meal-value", "Sachbezugswert Hauptmahlzeit");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-meal-value", "Sachbezugswert Hauptmahlzeit");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-employer-meal-grant", "Employer Meal Grant");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-employer-meal-grant", "AG Essenszuschuss");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-employer-meal-grant", "AG Essenszuschuss");
        $this->langVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Meal Value + Employer Meal Grant");
        $this->langVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Wert digitale Essensmarke");
        $this->langVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Wert digitale Essensmarke");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableSalutation", "Salutation");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableSalutation", "Anrede");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableSalutation", "Anrede");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableCompanyName", "Company Name");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableCompanyName", "Unternehmensname");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableCompanyName", "Unternehmensname");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableCompanyStreet", "Company Street");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableCompanyStreet", "Straße Unternehmen");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableCompanyStreet", "Straße Unternehmen");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableCompanyHouse", "Company House");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableCompanyHouse", "Hausnr. Unternehmen");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableCompanyHouse", "Hausnr. Unternehmen");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableCompanyZipCode", "Company Zip Code");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableCompanyZipCode", "Postleitzahl Unternehmens");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableCompanyZipCode", "Postleitzahl Unternehmens");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableCompanyCity", "Company City");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableCompanyCity", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableCompanyCity", "Stadt Unternehmen");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeFirstName", "First Name");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeFirstName", "Vorname");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeFirstName", "Vorname");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeLastName", "Last Name");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeLastName", "Nachname");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeLastName", "Nachname");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeBirthday", "Birthdate");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeBirthday", "Geburtsdatum");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeBirthday", "Geburtsdatum");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeStreet", "Street");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeStreet", "Straße");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeStreet", "Straße");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeHouse", "House");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeHouse", "Hausnr.");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeHouse", "Hausnr.");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeZipCode", "Zip Code");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeZipCode", "Postleitzahl");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeZipCode", "Postleitzahl");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeCity", "City");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeCity", "Stadt");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeCity", "Stadt");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployeeWorkPlace", "Place Of Work");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployeeWorkPlace", "Beschäftigungsort");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployeeWorkPlace", "Beschäftigungsort");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableContractStartDate", "Start Date Service");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableContractStartDate", "Start Datum Sachlohnart");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableContractStartDate", "Start Datum Sachlohnart");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableMonthlyAmount", "Monthly € Amount");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableMonthlyAmount", "Monatl. € Betrag");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableMonthlyAmount", "Monatl. € Betrag");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableUnitsPerMonth", "Units Per Month");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableUnitsPerMonth", "Max. Einheiten pro Monat");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableUnitsPerMonth", "Max. Einheiten pro Monat");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableMealValue", "Meal Value");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableMealValue", "Sachbezugswert Hauptmahlzeit");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableMealValue", "Sachbezugswert Hauptmahlzeit");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableEmployerMealGrant", "Employer Meal Grant");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableEmployerMealGrant", "AG Essenszuschuss");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableEmployerMealGrant", "AG Essenszuschuss");

        $this->delLangVarList[] = new LangVar("en", "template", "agreements", "contracts.html", "VariableMealValueEmployerMealGrant", "Meal Value + Employer Meal Grant");
        $this->delLangVarList[] = new LangVar("de", "template", "agreements", "contracts.html", "VariableMealValueEmployerMealGrant", "Wert digitale Essensmarke");
        $this->delLangVarList[] = new LangVar("tr", "template", "agreements", "contracts.html", "VariableMealValueEmployerMealGrant", "Wert digitale Essensmarke");
    }

    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }

        foreach($this->delLangVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
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
