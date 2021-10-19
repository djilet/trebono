<?php

use Phinx\Migration\AbstractMigration;

class ConfigInvoiceSortOrder extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE config SET sort_order='1' WHERE code='export_datev_client_id'");
        $this->execute("UPDATE config SET sort_order='2' WHERE code='export_datev_lug_ini'");
        $this->execute("UPDATE config SET sort_order='3' WHERE code='export_datev_tax_consultant_id'");
        $this->execute("UPDATE config SET sort_order='4' WHERE code='export_datev_voucher_client_id'");

        $this->execute("UPDATE config SET sort_order='1' WHERE code='do_not_use_ocr'");
        $this->execute("UPDATE config SET sort_order='2' WHERE code='givve_transactions_month_limit'");
        $this->execute("UPDATE config SET sort_order='3' WHERE code='send_mail'");
        $this->execute("UPDATE config SET sort_order='4' WHERE code='send_mail_hour_limit'");
        $this->execute("UPDATE config SET sort_order='5' WHERE code='send_mail_stop'");
        $this->execute("UPDATE config SET sort_order='6' WHERE code='voucher_reason'");
        $this->execute("UPDATE config SET sort_order='7' WHERE code='voucher_sets_of_goods'");

        $this->execute("UPDATE config SET sort_order='1' WHERE code='invoice_vat'");
        $this->execute("UPDATE config SET sort_order='2' WHERE code='voucher_invoice_vat'");
        $this->execute("UPDATE config SET sort_order='3' WHERE code='receipt_verificator_payment_review'");
        $this->execute("UPDATE config SET sort_order='4' WHERE code='receipt_verificator_payment_supervisor'");
        $this->execute("UPDATE config SET sort_order='5' WHERE code='receipt_verificator_payment_approve_proposed'");
        $this->execute("UPDATE config SET sort_order='6' WHERE code='receipt_verificator_payment_denied'");

        $this->execute("UPDATE config SET sort_order='1' WHERE code='app_guideline'");
        $this->execute("UPDATE config SET sort_order='2' WHERE code='app_license'");
        $this->execute("UPDATE config SET sort_order='3' WHERE code='app_org_guideline'");
        $this->execute("UPDATE config SET sort_order='4' WHERE code='get_started_document'");
        $this->execute("UPDATE config SET sort_order='5' WHERE code='business_terms_1'");
        $this->execute("UPDATE config SET sort_order='6' WHERE code='business_terms_2'");
        $this->execute("UPDATE config SET sort_order='7' WHERE code='business_terms_3'");
        $this->execute("UPDATE config SET sort_order='8' WHERE code='business_terms_4'");
        $this->execute("UPDATE config SET sort_order='9' WHERE code='business_terms_5'");
        $this->execute("UPDATE config SET sort_order='10' WHERE code='business_terms_6'");
    }

    public function down()
    {
        $this->execute("UPDATE config SET sort_order='0' WHERE code='export_datev_client_id'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='export_datev_lug_ini'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='export_datev_tax_consultant_id'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='export_datev_voucher_client_id'");

        $this->execute("UPDATE config SET sort_order='0' WHERE code='do_not_use_ocr'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='givve_transactions_month_limit'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='send_mail'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='send_mail_hour_limit'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='send_mail_stop'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='voucher_reason'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='voucher_sets_of_goods'");

        $this->execute("UPDATE config SET sort_order='0' WHERE code='invoice_vat'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='voucher_invoice_vat'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='receipt_verificator_payment_review'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='receipt_verificator_payment_supervisor'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='receipt_verificator_payment_approve_proposed'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='receipt_verificator_payment_denied'");

        $this->execute("UPDATE config SET sort_order='0' WHERE code='app_guideline'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='app_license'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='app_org_guideline'");
        $this->execute("UPDATE config SET sort_order='0' WHERE code='get_started_document'");
        $this->execute("UPDATE config SET sort_order='5' WHERE code='business_terms_1'");
        $this->execute("UPDATE config SET sort_order='6' WHERE code='business_terms_2'");
        $this->execute("UPDATE config SET sort_order='7' WHERE code='business_terms_3'");
        $this->execute("UPDATE config SET sort_order='8' WHERE code='business_terms_4'");
        $this->execute("UPDATE config SET sort_order='9' WHERE code='business_terms_5'");
        $this->execute("UPDATE config SET sort_order='10' WHERE code='business_terms_6'");
    }
}
