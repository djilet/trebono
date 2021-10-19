<?php

use Phinx\Migration\AbstractMigration;

class LanguageVatReport extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "VatReport", "VAT Report");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "VatReport", "Umsatzsteuerbericht");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "VatReport", "Umsatzsteuerbericht");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportTimeFrame", "Time Frame:");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportTimeFrame", "Zeitraum:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportTimeFrame", "Zeitraum:");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ReportProductGroup", "Voucher Type:");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ReportProductGroup", "Gutschein Art:");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ReportProductGroup", "Gutschein Art:");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "PaidAmount", "Paid amount");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "PaidAmount", "Umsatzsteuer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "PaidAmount", "Umsatzsteuer");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "ApprovedAmount", "Approved amount");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "ApprovedAmount", "Vorsteuer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "ApprovedAmount", "Vorsteuer");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "CheckAmount", "Difference");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "CheckAmount", "Differenz (Guthaben+ / Zahlung -)");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "CheckAmount", "Differenz (Guthaben+ / Zahlung -)");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "GrossValue", "Gross value");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "GrossValue", "Brutto Wert");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "GrossValue", "Brutto Wert");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "Tax", "Tax");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "Tax", "Steuer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "Tax", "Steuer");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "NetValue", "Net value");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "NetValue", "Netto Wert");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "NetValue", "Netto Wert");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard.html", "Total", "Total");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard.html", "Total", "Gesamt");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard.html", "Total", "Gesamt");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsExported", "VAT Report: paid details");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsExported", "Umsatzsteuerbericht: Details von Umsatzsteuer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsExported", "Umsatzsteuerbericht: Details von Umsatzsteuer");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsApproved", "VAT Report: approved details");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsApproved", "Umsatzsteuerbericht: Details von Vorsteuer");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "VatReportDetailsApproved", "Umsatzsteuerbericht: Details von Vorsteuer");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "LegalReceiptID", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "LegalReceiptID", "Beleg ID");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "LegalReceiptID", "Beleg ID");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "Amount", "Amount");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "Amount", "Genehmigter Betrag");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "Amount", "Genehmigter Betrag");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "Vat", "VAT");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "Vat", "VAT");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "Vat", "VAT");

        $this->langVarList[] = new LangVar("en", "template", "core", "voucher_dashboard_vat_details.html", "VoucherType", "Service");
        $this->langVarList[] = new LangVar("de", "template", "core", "voucher_dashboard_vat_details.html", "VoucherType", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "core", "voucher_dashboard_vat_details.html", "VoucherType", "Service");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "voucher-dashboard-section-vat_report", "VAT report");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "voucher-dashboard-section-vat_report", "Umsatzsteuerbericht");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "voucher-dashboard-section-vat_report", "Umsatzsteuerbericht");
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
