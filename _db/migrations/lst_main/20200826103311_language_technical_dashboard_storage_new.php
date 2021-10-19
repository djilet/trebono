<?php

use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardStorageNew extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "api_receipt_log", "api_receipt_log");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "api_receipt_log", "api_receipt_log");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "api_receipt_log", "api_receipt_log");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "invoice_export_csv", "invoice_export_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "invoice_export_csv", "invoice_export_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "invoice_export_csv", "invoice_export_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "voucher_export_csv", "voucher_export_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "voucher_export_csv", "voucher_export_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "voucher_export_csv", "voucher_export_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "stored_data_zip", "stored_data_zip");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "stored_data_zip", "stored_data_zip");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "stored_data_zip", "stored_data_zip");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "master_data", "master_data");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "master_data", "master_data");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "master_data", "master_data");
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
