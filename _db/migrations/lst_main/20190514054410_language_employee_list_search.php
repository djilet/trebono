<?php


use Phinx\Migration\AbstractMigration;

class LanguageEmployeeListSearch extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "Option", "Field");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "Option", "Feld");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "Option", "Feld");

        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "OptionValue", "Field value");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "OptionValue", "Feldwert");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "OptionValue", "Feldwert");
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
