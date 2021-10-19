<?php

use Phinx\Migration\AbstractMigration;

class LanguageInvoiceHistory extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "RevisionHistory", "rev. history");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "RevisionHistory", "Historie");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "RevisionHistory", "Historie");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "Active", "Active");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "Active", "Aktiv");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "Active", "Aktiv");

        $this->langVarList[] = new LangVar("en", "php", "billing", "common", "Cancel", "Cancel");
        $this->langVarList[] = new LangVar("de", "php", "billing", "common", "Cancel", "Stornieren");
        $this->langVarList[] = new LangVar("tr", "php", "billing", "common", "Cancel", "Stornieren");
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
