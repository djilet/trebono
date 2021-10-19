<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeReceiptList extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptID", "Receipt ID");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptID", "Beleg ID:");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptID", "Beleg ID:");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "Created", "Created");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "Created", "Erstellt am:");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "Created", "Erstellt am:");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptDate", "Receipt Date");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptDate", "Datum des Beleges");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "ReceiptDate", "Datum des Beleges");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "ApprovedVal", "Approved Proposed/Approved units");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "ApprovedVal", "Genehmigter Betrag / Einheiten");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "ApprovedVal", "Genehmigter Betrag / Einheiten");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "Service", "Service");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "Service", "Service");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "Service", "Service");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list_table.html", "Status", "Status");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list_table.html", "Status", "Aktueller Status:");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list_table.html", "Status", "Aktueller Status:");
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
