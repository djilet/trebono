<?php


use Phinx\Migration\AbstractMigration;

class LanguageBookedServiceFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_list.html", "HasActiveContractFor", "Booked service");
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_list.html", "HasActiveContractFor", "Gebuchter Service");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_list.html", "HasActiveContractFor", "Gebuchter Service");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "HasActiveContractFor", "Booked service:");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "HasActiveContractFor", "Gebuchter Service");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "HasActiveContractFor", "Gebuchter Service");
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
