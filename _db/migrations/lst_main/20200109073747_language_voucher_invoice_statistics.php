<?php

use Phinx\Migration\AbstractMigration;

class LanguageVoucherInvoiceStatistics extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "core", "dashboard.html", "VoucherInvoiceRecurringStatistics", "Gutschein Rechnung Umsatz");
        $this->langVarList[] = new LangVar("en", "template", "core", "dashboard.html", "VoucherInvoiceRecurringStatistics", "Voucher invoices statistics");
        $this->langVarList[] = new LangVar("tr", "template", "core", "dashboard.html", "VoucherInvoiceRecurringStatistics", "Gutschein Rechnung Umsatz");
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
