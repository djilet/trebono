<?php

use Phinx\Migration\AbstractMigration;

class LanguageMasterDataUpdateButtons extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataServiceUpdate", "Update Organization unit: Master data export Service");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataServiceUpdate", "Aktualisieren Unternehmen: Service RG Einzug");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataServiceUpdate", "Aktualisieren Unternehmen: Service RG Einzug");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucherUpdate", "Update Organization unit: Master data export Voucher");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucherUpdate", "Aktualisieren Unternehmen: Gutschein RG Einzug");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucherUpdate", "Aktualisieren Unternehmen: Gutschein RG Einzug");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaMasterDataServiceUpdate", "Update Org Unit: Master data Export SEPA Service");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaMasterDataServiceUpdate", "Aktualisieren Unternehmen: SEPA Service RG");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaMasterDataServiceUpdate", "Aktualisieren Unternehmen: SEPA Service RG");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaMasterDataVoucherUpdate", "Update Org Unit: Master data Export SEPA Voucher");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaMasterDataVoucherUpdate", "Aktualisieren Unternehmen: SEPA Gutschein RG");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaMasterDataVoucherUpdate", "Aktualisieren Unternehmen: SEPA Gutschein RG");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "EmployeeMasterDataUpdate", "Update Employee: Master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "EmployeeMasterDataUpdate", "Aktualisieren Mitarbeiter: Gutschein Zahlungen");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "EmployeeMasterDataUpdate", "Aktualisieren Mitarbeiter: Gutschein Zahlungen");


        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "company_unit_service_update-type", "Update Master Data Company Account for Services Invoice");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "company_unit_service_update-type", "Aktualisieren Kunden: Stammdaten Anlage Kontoanlage Service Rechnungen");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "company_unit_service_update-type", "Aktualisieren Kunden: Stammdaten Anlage Kontoanlage Service Rechnungen");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "company_unit_voucher_update-type", "Update Master Data Company Account for Voucher Invoice");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "company_unit_voucher_update-type", "Aktualisieren Kunden: Stammdaten Kontoanlage Gutschein Rechnungen");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "company_unit_voucher_update-type", "Aktualisieren Kunden: Stammdaten Kontoanlage Gutschein Rechnungen");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "sepa_service_update-type", "Update Org Unit: SEPA export Service Invoices");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "sepa_service_update-type", "Aktualisieren Kunden: SEPA Stammdaten Anlage Service Rechnung");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "sepa_service_update-type", "Aktualisieren Kunden: SEPA Stammdaten Anlage Service Rechnung");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "sepa_voucher_update-type", "Update Org Unit: SEPA export Voucher invoices");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "sepa_voucher_update-type", "Aktualisieren Kunden: SEPA Stammdaten Anlage Gutschein Rechnung");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "sepa_voucher_update-type", "Aktualisieren Kunden: SEPA Stammdaten Anlage Gutschein Rechnung");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "employee_update-type", "Update Master Data Employee Account for Voucher payments");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "employee_update-type", "Aktualisieren Mitarbeiter: Stammdaten Anlage Konto für Gutschein Zahlung");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "employee_update-type", "Aktualisieren Mitarbeiter: Stammdaten Anlage Konto für Gutschein Zahlung");
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
