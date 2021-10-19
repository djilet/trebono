<?php

use Phinx\Migration\AbstractMigration;

class LanguageTechnicalDashboardStorage extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "core", "technical_dashboard.html", "Total", "Total");
        $this->langVarList[] = new LangVar("de", "template", "core", "technical_dashboard.html", "Total", "Gesamt");
        $this->langVarList[] = new LangVar("tr", "template", "core", "technical_dashboard.html", "Total", "Gesamt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "error_log", "error_log");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "error_log", "error_log");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "error_log", "error_log");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "agreements_pdf", "agreements_pdf");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "agreements_pdf", "vereinbarungen_pdf");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "agreements_pdf", "vereinbarungen_pdf");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "invoice_pdf", "invoice_pdf");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "invoice_pdf", "rechnung_pdf");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "invoice_pdf", "rechnung_pdf");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "company_apps_img", "company_apps_img");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "company_apps_img", "unternehmens_apps_img");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "company_apps_img", "unternehmens_apps_img");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_bookkeeping_csv", "payroll_bookkeeping_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_bookkeeping_csv", "lohnbuchhaltung_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_bookkeeping_csv", "lohnbuchhaltung_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_LOGGA_csv", "payroll_LOGGA_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_LOGGA_csv", "lohnbuchhaltung_LOGGA_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_LOGGA_csv", "lohnbuchhaltung_LOGGA_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_pdf", "payroll_pdf");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_pdf", "lohnbuchhaltung_pdf");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_pdf", "lohnbuchhaltung_pdf");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_topas_csv", "payroll_topas_csv");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_topas_csv", "lohnbuchhaltung_topas_csv");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_topas_csv", "lohnbuchhaltung_topas_csv");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_Lodas_txt", "payroll_Lodas_txt");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_Lodas_txt", "lohnbuchhaltung_Lodas_txt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_Lodas_txt", "lohnbuchhaltung_Lodas_txt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "payroll_Lug_txt", "payroll_Lug_txt");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "payroll_Lug_txt", "lohnbuchhaltung_Lug_txt");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "payroll_Lug_txt", "lohnbuchhaltung_Lug_txt");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "voucher_pdf", "voucher_pdf");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "voucher_pdf", "gutschein_pdf");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "voucher_pdf", "gutschein_pdf");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "config", "config");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "config", "config");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "config", "config");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "mail", "mail");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "mail", "mail");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "mail", "mail");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "partner_report_xlsx", "partner_report_xlsx");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "partner_report_xlsx", "partnerbericht_xlsx");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "partner_report_xlsx", "partnerbericht_xlsx");


        $this->langVarList[] = new LangVar("en", "php", "core", "common", "product_img", "product_img");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "product_img", "produkt_img");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "product_img", "produkt_img");


        $this->langVarList[] = new LangVar("en", "php", "core", "common", "reciept", "reciept");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "reciept", "beleg");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "reciept", "beleg");


        $this->langVarList[] = new LangVar("en", "php", "core", "common", "user_img", "user_img");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "user_img", "benutzer_img");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "user_img", "benutzer_img");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "other", "other");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "other", "andere");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "other", "andere");


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
