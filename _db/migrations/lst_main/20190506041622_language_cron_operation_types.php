<?php


use Phinx\Migration\AbstractMigration;

class LanguageCronOperationTypes extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-invoice_create", "Invoice creation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-invoice_create", "Rechnungserstellung");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-invoice_create", "Rechnungserstellung");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-payroll_create", "Payroll creation");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-payroll_create", "Gehaltsabrechnung");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-payroll_create", "Gehaltsabrechnung");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deactivation of employee");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deaktivierung des Mitarbeiters");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-employee_deactivate", "Deaktivierung des Mitarbeiters");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-voucher", "Vouchers");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-voucher", "Gutscheine");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-voucher", "Gutscheine");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-receipt_clean", "Clean receipts");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-receipt_clean", "Bereinigung der Quittungen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-receipt_clean", "Bereinigung der Quittungen");

        $this->langVarList[] = new LangVar("en", "php", "core", "common", "operation-cron-section-push_notification", "Push notifications");
        $this->langVarList[] = new LangVar("de", "php", "core", "common", "operation-cron-section-push_notification", "Mitteilungen");
        $this->langVarList[] = new LangVar("tr", "php", "core", "common", "operation-cron-section-push_notification", "Mitteilungen");
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
