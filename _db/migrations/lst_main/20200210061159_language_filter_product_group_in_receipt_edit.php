<?php

use Phinx\Migration\AbstractMigration;

class LanguageFilterProductGroupInReceiptEdit extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusNew", "New");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusNew", "Neu");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusNew", "Neu");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusReview", "In review");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusReview", "In Bearbeitung");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusReview", "In Bearbeitung");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusSupervisor", "For supervisor");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusSupervisor", "für Vorgesetzten");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusSupervisor", "für Vorgesetzten");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusApproveProposed", "Approve proposed");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusApproveProposed", "Beleg bestätigen");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusApproveProposed", "Beleg bestätigen");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusApproved", "Approved");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusApproved", "Eingereicht Abrechnung");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusApproved", "Eingereicht Abrechnung");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "block_employee_receipt_list.html", "StatusDenied", "Denied");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "block_employee_receipt_list.html", "StatusDenied", "Nicht verwendbar");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "block_employee_receipt_list.html", "StatusDenied", "Nicht verwendbar");
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
