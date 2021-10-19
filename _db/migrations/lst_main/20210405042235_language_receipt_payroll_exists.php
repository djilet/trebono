<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptPayrollExists extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "php", "receipt", "common", "receipt-payroll-exists", "Payroll for this receipt date is closed and this employee already got paid for a receipt. No receipts can be accepted for this month anymore.");
        $this->langVarList[] = new LangVar("de", "php", "receipt", "common", "receipt-payroll-exists", "Die Lohnabrechnung ist bereits erstellt und eine Zahlung wurde vorgenommen. Deswegen kann kein Beleg mehr mit dem Beleg Datum akzeptiert werden.");
        $this->langVarList[] = new LangVar("tr", "php", "receipt", "common", "receipt-payroll-exists", "Die Lohnabrechnung ist bereits erstellt und eine Zahlung wurde vorgenommen. Deswegen kann kein Beleg mehr mit dem Beleg Datum akzeptiert werden.");
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
