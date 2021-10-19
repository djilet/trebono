<?php


use Phinx\Migration\AbstractMigration;

class EmployeeLastApiCallLangVar extends AbstractMigration
{
    private $newLangVarList = array();
    
    public function init()
    {
        $this->newLangVarList[] = new LangVar("en", "template", "company", "employee_list.html", "LastApiCall", "Last activity");
        $this->newLangVarList[] = new LangVar("de", "template", "company", "employee_list.html", "LastApiCall", "Letzte Aktivität");
        $this->newLangVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "LastApiCall", "Letzte Aktivität");
    }
    
    public function up()
    {
        foreach($this->newLangVarList as $langVar)
        {
            $this->execute($langVar->GetInsertQuery());
        }
    }
    
    public function down()
    {
        foreach($this->newLangVarList as $langVar)
        {
            $this->execute($langVar->GetDeleteQuery());
        }
    }
}
