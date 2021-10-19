<?php

use Phinx\Migration\AbstractMigration;

class ResetEmployeePayment extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {

        $this->langVarList[] = new LangVar("en", "template", "billing", "payroll_list.html", "CreditorExportReset", "Employee payment export reset");
        $this->langVarList[] = new LangVar("de", "template", "billing", "payroll_list.html", "CreditorExportReset", "Mitarbeiterzahlung Export zurückgesetzt");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "payroll_list.html", "CreditorExportReset", "Mitarbeiterzahlung Export zurückgesetzt");
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
