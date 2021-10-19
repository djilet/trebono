<?php

use Phinx\Migration\AbstractMigration;

class VoucherDashboard extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "admin-menu-voucher_dashboard", "Gutschein Dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "admin-menu-voucher_dashboard", "Voucher Dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "admin-menu-voucher_dashboard", "Gutschein Dashboard");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "title-voucher_dashboard", "Gutschein Dashboard");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "title-voucher_dashboard", "Voucher Dashboard");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "title-voucher_dashboard", "Gutschein Dashboard");

        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherDashboard", "Gutschein Dashboard");
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherDashboard", "Voucher Dashboard");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherDashboard", "Gutschein Dashboard");

        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "YearlyVoucherStatistics", "Gutschein jährliche Statistik");
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "YearlyVoucherStatistics", "Voucher yearly statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "YearlyVoucherStatistics", "Gutschein jährliche Statistik");

        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherStatisticsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherStatisticsShow", "Show");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherStatisticsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "StatisticsTotal", "Gesamt");
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "StatisticsTotal", "Total");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "StatisticsTotal", "Gesamt");

        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherType", "Belegart");
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherType", "Voucher type");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherType", "Belegart");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "issued", "Erteilen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "issued", "Issued");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "issued", "Erteilen");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "invoiced", "In Rechnung gestellt");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "invoiced", "Invoiced");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "invoiced", "In Rechnung gestellt");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "paid-to-employee", "An Mitarbeiter bezahlt");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "paid-to-employee", "Paid to employee");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "paid-to-employee", "An Mitarbeiter bezahlt");

        $this->langVarList[] = new LangVar("de", "php", "core", "common", "open", "Öffnen");
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "open", "Open");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "open", "Öffnen");
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
