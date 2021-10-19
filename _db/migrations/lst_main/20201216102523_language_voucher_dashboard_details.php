<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherDashboardDetails extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsNotUsed", "Open amount on not used vouchers");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsNotUsed", "Offener Betrag für nicht verwendete Gutscheine");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsNotUsed", "Offener Betrag für nicht verwendete Gutscheine");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsPartiallyUsed", "Open amount on partially used vouchers");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsPartiallyUsed", "Offener Betrag auf teilweise genutzten Gutscheinen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "VoucherDashboardDetailsPartiallyUsed", "Offener Betrag auf teilweise genutzten Gutscheinen");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "Employee", "Employee");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "Employee", "Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "Employee", "Mitarbeiter");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "StatisticsTotal", "Total");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "StatisticsTotal", "Gesamt");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "StatisticsTotal", "Gesamt");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "Expand", "Details");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "Expand", "Details");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_details.html", "Collapse", "Collapse");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_details.html", "Collapse", "Einklappen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_details.html", "Collapse", "Einklappen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "invoiced-issued-month", "Invoiced on the issuing month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "invoiced-issued-month", "Abgerechnet am Ausstellungsmonat");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "invoiced-issued-month", "Abgerechnet am Ausstellungsmonat");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "invoiced-not-issued-month", "Invoiced not on the issuing month");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "invoiced-not-issued-month", "Wird nicht im Ausstellungsmonat in Rechnung gestellt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "invoiced-not-issued-month", "Wird nicht im Ausstellungsmonat in Rechnung gestellt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "open-not-used", "Amount of not used");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "open-not-used", "Menge nicht verwendet");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "open-not-used", "Menge nicht verwendet");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "open-partially-used", "Open amount of partially used");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "open-partially-used", "Offene Menge von teilweise verwendet");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "open-partially-used", "Offene Menge von teilweise verwendet");
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
