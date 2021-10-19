<?php

use Phinx\Migration\AbstractMigration;

class LanguageMasterDataSepa extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaMasterDataService", "Masterdata Export SEPA Service");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaMasterDataService", "Stammdatenexport SEPA-Service");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaMasterDataService", "Stammdatenexport SEPA-Service");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaMasterDataVoucher", "Masterdata Export SEPA Voucher");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaMasterDataVoucher", "Stammdatenexport SEPA Voucher");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaMasterDataVoucher", "Stammdatenexport SEPA Voucher");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "sepa_service-type", "Sepa service");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "sepa_service-type", "Sepa service");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "sepa_service-type", "Sepa service");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "sepa_voucher-type", "Sepa voucher");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "sepa_voucher-type", "Sepa gutschein");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "sepa_voucher-type", "Sepa gutschein");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaServiceMasterData", "SEPA Service Master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaServiceMasterData", "SEPA Service Stammdatenexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaServiceMasterData", "SEPA Service Stammdatenexport");

        $this->langVarList[] = new LangVar("en", "template", "billing", "master_data_list.html", "SepaVoucherMasterData", "SEPA Voucher Master data export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "master_data_list.html", "SepaVoucherMasterData", "SEPA gutschein Stammdatenexport");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "master_data_list.html", "SepaVoucherMasterData", "SEPA gutschein Stammdatenexport");
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
