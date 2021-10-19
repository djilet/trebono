<?php

use Phinx\Migration\AbstractMigration;

class BenefitVoucherCompanyStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "TabStatistics", "Statistiken");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "TabStatistics", "Statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "TabStatistics", "Statistiken");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Sachbezug Gutschein Statistik");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Benefit Voucher Statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Sachbezug Gutschein Statistik");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Sachbezug Gutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Benefit Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Sachbezug Gutschein jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Show");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "StatisticAccessNote", "Unternehmen / Abteilung speichern, um auf Statistiken zuzugreifen");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "StatisticAccessNote", "Save company/department to access statistics");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "StatisticAccessNote", "Unternehmen / Abteilung speichern, um auf Statistiken zuzugreifen");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "VoucherAmount", "Betrag");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "VoucherAmount", "Amount");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "VoucherAmount", "Betrag");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "StatisticsTotal", "Gesamt");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "StatisticsTotal", "Total");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "StatisticsTotal", "Gesamt");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "Collapse", "Zusammenbruch");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "Collapse", "Collapse");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "Collapse", "Zusammenbruch");

        $this->langVarList[] = new LangVar("de", "template", "company", "block_voucher_statistics.html", "Expand", "Erweitern");
        $this->langVarList[] = new LangVar("en", "template", "company", "block_voucher_statistics.html", "Expand", "Expand");
        $this->langVarList[] = new LangVar("tr", "template", "company", "block_voucher_statistics.html", "Expand", "Erweitern");
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
