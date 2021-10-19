<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeReceiptFilterError extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "FilterReceiptEmptyProduct", "Please, choose product for receipt filter");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "FilterReceiptEmptyProduct", "Bitte wählen Sie ein Produkt für den Belegfilter");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "FilterReceiptEmptyProduct", "Bitte wählen Sie ein Produkt für den Belegfilter");
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
