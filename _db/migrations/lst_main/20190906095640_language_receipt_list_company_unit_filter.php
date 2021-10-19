<?php

use Phinx\Migration\AbstractMigration;

class LanguageReceiptListCompanyUnitFilter extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterCompanyTitle", "Unternehmen");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterCompanyTitle", "Company Name");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterCompanyTitle", "Unternehmen");

        $this->langVarList[] = new LangVar("de", "template", "receipt", "receipt_list.html", "FilterCompanyTitlePlaceholder", "Suche");
        $this->langVarList[] = new LangVar("en", "template", "receipt", "receipt_list.html", "FilterCompanyTitlePlaceholder", "Enter Company Name");
        $this->langVarList[] = new LangVar("tr", "template", "receipt", "receipt_list.html", "FilterCompanyTitlePlaceholder", "Suche");
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
