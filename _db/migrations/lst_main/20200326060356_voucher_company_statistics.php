<?php

use Phinx\Migration\AbstractMigration;

class VoucherCompanyStatistics extends AbstractMigration
{
    private $langVarList = array();
    private $delangVarList = array();

    public function init()
    {
        $this->delangVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Sachbezug Gutschein Statistik");
        $this->delangVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Benefit Voucher Statistics");
        $this->delangVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherStatistics", "Sachbezug Gutschein Statistik");

        $this->delangVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Sachbezug Gutschein jährliche Statistik");
        $this->delangVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Benefit Voucher yearly statistics");
        $this->delangVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherYearlyStatistics", "Sachbezug Gutschein jährliche Statistik");

        $this->delangVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Anzeigen");
        $this->delangVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Show");
        $this->delangVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "BenefitVoucherStatisticsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-statistics", "Essensgutschein Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-statistics", "Food Voucher Statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-statistics", "Essensgutschein Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-yearly-statistics", "Essensgutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-yearly-statistics", "Benefit Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__FOOD_VOUCHER."-yearly-statistics", "Essensgutschein jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-statistics", "Sachbezug Gutschein Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-statistics", "Benefit Voucher Statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-statistics", "Sachbezug Gutschein Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-yearly-statistics", "Sachbezug Gutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-yearly-statistics", "Benefit Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BENEFIT_VOUCHER."-yearly-statistics", "Sachbezug Gutschein jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__GIFT."-statistics", "Geschenke Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__GIFT."-statistics", "Gifts Voucher Statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__GIFT."-statistics", "Geschenke Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__GIFT."-yearly-statistics", "Geschenke jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__GIFT."-yearly-statistics", "Gifts Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__GIFT."-yearly-statistics", "Geschenke jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BONUS."-statistics", "Bonus Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BONUS."-statistics", "Bonus Voucher Statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BONUS."-statistics", "Bonus Statistik");

        $this->langVarList[] = new LangVar("de", "php", "company", "common", PRODUCT_GROUP__BONUS."-yearly-statistics", "Bonus jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "php", "company", "common", PRODUCT_GROUP__BONUS."-yearly-statistics", "Bonus Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "php", "company", "common", PRODUCT_GROUP__BONUS."-yearly-statistics", "Bonus jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "VoucherStatisticsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "VoucherStatisticsShow", "Show");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "VoucherStatisticsShow", "Anzeigen");
    }
    public function up()
    {
        foreach($this->langVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
        foreach($this->delangVarList as $langVar)
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
        foreach($this->delangVarList as $langVar)
        {
            $query = $langVar->GetInsertQuery();
            $this->execute($query);
        }
    }
}
