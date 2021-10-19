<?php


use Phinx\Migration\AbstractMigration;

class LanguageSalaryOption extends AbstractMigration
{
    
    private $langVarList = array();
    
    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "company", "company_unit_edit.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("en", "template", "company", "company_unit_edit.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("tr", "template", "company", "company_unit_edit.html", "Wmax", "W max");
        
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_edit.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_edit.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_edit.html", "Wmax", "W max");
        
        $this->langVarList[] = new LangVar("de", "template", "product", "option_list.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("en", "template", "product", "option_list.html", "Wmax", "W max");
        $this->langVarList[] = new LangVar("tr", "template", "product", "option_list.html", "Wmax", "W max");
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
