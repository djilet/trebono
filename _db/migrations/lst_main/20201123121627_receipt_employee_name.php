<?php

use Phinx\Migration\AbstractMigration;

class ReceiptEmployeeName extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "EmployeeName", "Employee name");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "EmployeeName", "Mitarbeitername");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "EmployeeName", "Mitarbeitername");

        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_edit.html", "CompanyUnitTitle", "Company unit title");
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_edit.html", "CompanyUnitTitle", "Unternehmensname");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_edit.html", "CompanyUnitTitle", "Unternehmensname");
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
