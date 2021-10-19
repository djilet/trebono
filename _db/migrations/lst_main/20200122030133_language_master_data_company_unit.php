<?php

use Phinx\Migration\AbstractMigration;

class LanguageMasterDataCompanyUnit extends AbstractMigration
{
    private $langVarList = array();
    private $delLangVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataService", "Company unit Master data Service export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataService", "Unternehmenseinheit Stammdaten Serviceexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataService", "Unternehmenseinheit Stammdaten Serviceexport");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucher", "Company unit Master data Voucher export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucher", "Firmeneinheit Stammdaten Belegexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyUnitMasterDataVoucher", "Firmeneinheit Stammdaten Belegexport");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "employee-type", "Employee");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "employee-type", "Mitarbeiterin");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "employee-type", "Mitarbeiterin");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "company_unit_service-type", "Company unit Service");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "company_unit_service-type", "Unternehmenseinheit Service");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "company_unit_service-type", "Unternehmenseinheit Service");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "company_unit_voucher-type", "Company unit Voucher");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "company_unit_voucher-type", "Firmeneinheit Gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "company_unit_voucher-type", "Firmeneinheit Gutschein");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "master-data-no-company-units", "No new company units since the last master data export");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "master-data-no-company-units", "Keine neuen Unternehmenseinheiten seit dem letzten Stammdaten Export");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "master-data-no-company-units", "Keine neuen Kunden oder Mitarbeiter seit dem letzten Stammdaten Export");

        $this->delLangVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "CompanyMasterData", "Company Master data export");
        $this->delLangVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "CompanyMasterData", "Export von Firmenstammdaten");
        $this->delLangVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "CompanyMasterData", "Export von Firmenstammdaten");
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
