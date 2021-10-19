<?php

use Phinx\Migration\AbstractMigration;

class VoucherDashboardReports extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherStatisticsYearFilter", "Voucher year filter");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherStatisticsYearFilter", "Gutscheinjahresfilter");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherStatisticsYearFilter", "Gutscheinjahresfilter");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherDashboardReports", "Voucher reports");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherDashboardReports", "Gutscheinberichte");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherDashboardReports", "Gutscheinberichte");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsCompanyUnit", "Company unit");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsCompanyUnit", "Unternehmenseinheit");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsCompanyUnit", "Unternehmenseinheit");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsProductGroup", "Voucher type");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsProductGroup", "Belegart");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsProductGroup", "Belegart");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsStartDate", "Start date");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsStartDate", "Anfangsdatum");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsStartDate", "Anfangsdatum");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsEndDate", "End Date");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsEndDate", "Endtermin");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsEndDate", "Endtermin");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsShow", "Show");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsShow", "Anzeigen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsShow", "Anzeigen");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VoucherReportsCompany", "Company / Employee");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VoucherReportsCompany", "Unternehmen / Mitarbeiter");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VoucherReportsCompany", "Unternehmen / Mitarbeiter");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportsPaid", "Paid to employee");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportsPaid", "An Mitarbeiter bezahlt");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportsPaid", "An Mitarbeiter bezahlt");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportsToBePaid", "To be paid");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportsToBePaid", "Bezahlt werden");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportsToBePaid", "Bezahlt werden");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportsOpen", "Open");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportsOpen", "Öffnen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportsOpen", "Öffnen");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportsInvoiced", "Invoiced and deactivated");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportsInvoiced", "In Rechnung gestellt und deaktiviert");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportsInvoiced", "In Rechnung gestellt und deaktiviert");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportExpired", "Expired");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportExpired", "Abgelaufen");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportExpired", "Abgelaufen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "voucher-dashboard-section-statistics", "Voucher statistics");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "voucher-dashboard-section-statistics", "Gutschein Statistiken");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "voucher-dashboard-section-statistics", "Gutschein Statistiken");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "voucher-dashboard-section-reports", "Voucher reports");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "voucher-dashboard-section-reports", "Gutscheinberichte");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "voucher-dashboard-section-reports", "Gutscheinberichte");
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
