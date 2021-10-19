<?php

use Phinx\Migration\AbstractMigration;

class LanguageEmployeeListReceiptsCompare extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "company", "employee_list.html", "ProductGroupForReceipts", "Product for receipt filter");
        $this->langVarList[] = new LangVar("de", "template", "company", "employee_list.html", "ProductGroupForReceipts", "Produkt für Quittungsfilter");
        $this->langVarList[] = new LangVar("tr", "template", "company", "employee_list.html", "ProductGroupForReceipts", "Produkt für Quittungsfilter");
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
