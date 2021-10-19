<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherExportList extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "VoucherExportList", "Voucher Export List");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "VoucherExportList", "Exportliste für Gutscheine");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "MasterData", "Exportliste für Gutscheine");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "DownloadVoucherExport", "Download voucher export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "DownloadVoucherExport", "Gutschein exportieren");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "DownloadVoucherExport", "Gutschein exportieren");

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "VoucherExport", "Export");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "VoucherExport", "Export");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "VoucherExport", "Export");
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
