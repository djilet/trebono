<?php


use Phinx\Migration\AbstractMigration;

class LanguageReplacemetsFix extends AbstractMigration
{
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-payment_month", "Zahlung Monat");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-payment_month", "Payment month");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-payment_month", "Zahlung Monat");
        
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-amount_per_month", "Monatl. € Betrag");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-amount_per_month", "Monthly € Amount");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-amount_per_month", "Monatl. € Betrag");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-birthday", "Birthdate");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-birthday", "Geburtsdatum");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-city", "City");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-city", "Stadt");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company_city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-company-city", "Company City");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company_house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-company-house", "Company House");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-company-street", "Company Street");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-company-street", "Straße Unternehmen");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-company-title", "Company Name");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-company-title", "Unternehmensname");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-company-zip_code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-company-zip_code", "Company Zip Code");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-company-zip_code", "Postleitzahl Unternehmens");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-employer_meal_grant", "AG Essenszuschuss");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-employer_meal_grant", "Employer Meal Grant");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-employer_meal_grant", "AG Essenszuschuss");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-first_name", "Vorname");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-first_name", "First Name");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-first_name", "Vorname");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-house", "House");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-house", "Hausnr.");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-last_name", "Nachname");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-last_name", "Last Name");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-last_name", "Nachname");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-meal_value", "Sachbezugswert Hauptmahlzeit");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-meal_value", "Meal Value");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-meal_value", "Sachbezugswert Hauptmahlzeit");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-meal_value_employer_meal_grant", "Wert digitale Essensmarke");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-meal_value_employer_meal_grant", "Meal Value + Employer Meal Grant");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-meal_value_employer_meal_grant", "Wert digitale Essensmarke");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-salutation", "Salutation");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-salutation", "Anrede");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-start_date", "Start Datum Sachlohnart");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-start_date", "Start Date Service");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-start_date", "Start Datum Sachlohnart");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-street", "Street");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-street", "Straße");
        $this->langVarList[] = new LangVar("de", "php", "product", "common", "replacement-units_per_month", "Max. Einheiten pro Monat");
        $this->langVarList[] = new LangVar("en", "php", "product", "common", "replacement-units_per_month", "Units Per Month");
        $this->langVarList[] = new LangVar("tr", "php", "product", "common", "replacement-units_per_month", "Max. Einheiten pro Monat");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-work_place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-work_place", "Place Of Work");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-work_place", "Beschäftigungsort");
        $this->langVarList[] = new LangVar("de", "php", "company", "common", "replacement-zip_code", "Postleitzahl");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", "replacement-zip_code", "Zip Code");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", "replacement-zip_code", "Postleitzahl");
        
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-amount-per-month", "Monatl. € Betrag");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-amount-per-month", "Monthly € Amount");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-amount-per-month", "Monatl. € Betrag");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-birthday", "Geburtsdatum");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-birthday", "Birthdate");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-birthday", "Geburtsdatum");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-city", "Stadt");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-city", "City");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-city", "Stadt");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-city", "Company City");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-house", "Company House");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-street", "Straße Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-street", "Company Street");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-street", "Straße Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-title", "Unternehmensname");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-title", "Company Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-title", "Unternehmensname");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-company-zip-code", "Company Zip Code");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-employer-meal-grant", "AG Essenszuschuss");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-employer-meal-grant", "Employer Meal Grant");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-employer-meal-grant", "AG Essenszuschuss");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-first-name", "Vorname");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-first-name", "First Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-first-name", "Vorname");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-house", "Hausnr.");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-house", "House");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-house", "Hausnr.");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-last-name", "Nachname");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-last-name", "Last Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-last-name", "Nachname");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-meal-value", "Sachbezugswert Hauptmahlzeit");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-meal-value", "Meal Value");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-meal-value", "Sachbezugswert Hauptmahlzeit");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Wert digitale Essensmarke");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Meal Value + Employer Meal Grant");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-meal-value-employer-meal-grant", "Wert digitale Essensmarke");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-salutation", "Anrede");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-salutation", "Salutation");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-salutation", "Anrede");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-start-date", "Start Datum Sachlohnart");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-start-date", "Start Date Service");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-start-date", "Start Datum Sachlohnart");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-street", "Straße");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-street", "Street");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-street", "Straße");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-units-per-month", "Max. Einheiten pro Monat");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-units-per-month", "Units Per Month");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-units-per-month", "Max. Einheiten pro Monat");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-work-place", "Beschäftigungsort");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-work-place", "Place Of Work");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-work-place", "Beschäftigungsort");
        $this->delLangVarList[] = new LangVar("de", "php", "agreements", "common", "replacement-zip-code", "Postleitzahl");
        $this->delLangVarList[] = new LangVar("en", "php", "agreements", "common", "replacement-zip-code", "Zip Code");
        $this->delLangVarList[] = new LangVar("tr", "php", "agreements", "common", "replacement-zip-code", "Postleitzahl");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-birthday", "Geburtsdatum");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-birthday", "Birthdate");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-birthday", "Geburtsdatum");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-city", "Stadt");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-city", "City");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-city", "Stadt");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-city", "Company City");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-city", "Stadt Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-house", "Company House");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-house", "Hausnr. Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-street", "Straße Unternehmen");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-street", "Company Street");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-street", "Straße Unternehmen");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-title", "Unternehmensname");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-title", "Company Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-title", "Unternehmensname");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-company-zip-code", "Company Zip Code");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-company-zip-code", "Postleitzahl Unternehmens");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-first-name", "Vorname");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-first-name", "First Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-first-name", "Vorname");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-house", "Hausnr.");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-house", "House");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-house", "Hausnr.");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-last-name", "Nachname");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-last-name", "Last Name");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-last-name", "Nachname");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-salutation", "Anrede");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-salutation", "Salutation");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-salutation", "Anrede");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-street", "Straße");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-street", "Street");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-street", "Straße");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-work-place", "Beschäftigungsort");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-work-place", "Place Of Work");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-work-place", "Beschäftigungsort");
        $this->delLangVarList[] = new LangVar("de", "php", "core", "common", "replacement-zip-code", "Postleitzahl");
        $this->delLangVarList[] = new LangVar("en", "php", "core", "common", "replacement-zip-code", "Zip Code");
        $this->delLangVarList[] = new LangVar("tr", "php", "core", "common", "replacement-zip-code", "Postleitzahl");
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
