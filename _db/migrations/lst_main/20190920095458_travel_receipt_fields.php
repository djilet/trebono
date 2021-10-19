<?php

use Phinx\Migration\AbstractMigration;

class TravelReceiptFields extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "TravelVAT", "MwSt.-Satz");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "TravelVAT", "VAT");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "TravelVAT", "MwSt.-Satz");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "VATZero", "0%");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "VATZero", "0%");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "VATZero", "0%");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "VATSeven", "7%");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "VATSeven", "7%");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "VATSeven", "7%");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "VATNineteen", "19%");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "VATNineteen", "19%");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "VATNineteen", "19%");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Bookkeeping Account Information");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "LanMealAllowance", "LAN Verpflegungsmehraufwand");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "LanMealAllowance", "LAN Meal Allowance");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "LanMealAllowance", "LAN Verpflegungsmehraufwand");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccDailyAllowance", "ACC Tagesgeld");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccDailyAllowance", "ACC Daily Allowance");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccDailyAllowance", "ACC Tagesgeld");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccTicket", "Konto Fahrkarten");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccTicket", "ACC Ticket");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccTicket", "Konto Fahrkarten");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccAccommodation", "Konto Unterkunft");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccAccommodation", "ACC Accommodation");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccAccommodation", "Konto Unterkunft");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccCorporateHospitality", "Konto Bewirtung");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccCorporateHospitality", "ACC Corporate Hospitality");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccCorporateHospitality", "Konto Bewirtung");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccParking", "Konto Parken/Taxi");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccParking", "ACC Parking/Taxi");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccParking", "Konto Parken/Taxi");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccOtherCosts", "Konto Sonstige Kosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccOtherCosts", "ACC Other Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccOtherCosts", "Konto Sonstige Kosten");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccTravelCosts", "Konto Reisekosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccTravelCosts", "ACC Travel Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccTravelCosts", "Konto Reisekosten");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "AccCreditor", "Konto Kreditor");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "AccCreditor", "ACC Creditor");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "AccCreditor", "Konto Kreditor");


        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Bookkeeping Account Information");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "BookkeepingAccountInformation", "Buchhaltungskontoinformationen");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "LanMealAllowance", "LAN Verpflegungsmehraufwand");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "LanMealAllowance", "LAN Meal Allowance");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "LanMealAllowance", "LAN Verpflegungsmehraufwand");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccDailyAllowance", "ACC Tagesgeld");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccDailyAllowance", "ACC Daily Allowance");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccDailyAllowance", "ACC Tagesgeld");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccTicket", "Konto Fahrkarten");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccTicket", "ACC Ticket");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccTicket", "Konto Fahrkarten");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccAccommodation", "Konto Unterkunft");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccAccommodation", "ACC Accommodation");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccAccommodation", "Konto Unterkunft");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccCorporateHospitality", "Konto Bewirtung");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccCorporateHospitality", "ACC Corporate Hospitality");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccCorporateHospitality", "Konto Bewirtung");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccParking", "Konto Parken/Taxi");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccParking", "ACC Parking/Taxi");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccParking", "Konto Parken/Taxi");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccOtherCosts", "Konto Sonstige Kosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccOtherCosts", "ACC Other Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccOtherCosts", "Konto Sonstige Kosten");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccTravelCosts", "Konto Reisekosten");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccTravelCosts", "ACC Travel Costs");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccTravelCosts", "Konto Reisekosten");

        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "AccCreditor", "Konto Kreditor");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "AccCreditor", "ACC Creditor");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "AccCreditor", "Konto Kreditor");
    }

    public function up()
    {
        $this->table("receipt")
            ->addColumn("vat", "integer", ["null" => true])
            ->save();

        $this->table("company_unit")
            ->addColumn("lan_meal_allowance", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_daily_allowance", "string", ["length" => 255, "null" => true])

            ->addColumn("acc_ticket", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_accommodation", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_corporate_hospitality", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_parking", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_other_costs", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_travel_costs", "string", ["length" => 255, "null" => true])
            ->addColumn("acc_creditor", "string", ["length" => 255, "null" => true])
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }

    public function down()
    {
        $this->table("receipt")
            ->removeColumn("vat")
            ->save();

        $this->table("company_unit")
            ->removeColumn("lan_meal_allowance")
            ->removeColumn("acc_daily_allowance")

            ->removeColumn("acc_ticket")
            ->removeColumn("acc_accommodation")
            ->removeColumn("acc_corporate_hospitality")
            ->removeColumn("acc_parking")
            ->removeColumn("acc_other_costs")
            ->removeColumn("acc_travel_costs")
            ->removeColumn("acc_creditor")
            ->save();

        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetDeleteQuery();
            $this->execute($query);
        }
    }
}
