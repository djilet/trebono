<?php


use Phinx\Migration\AbstractMigration;

class LanguageMigrationInvoiceForOneCompany extends AbstractMigration
{
    private $langVarList = array();

    public function init()
    {
        $this->langVarList[] = new LangVar("en", "template", "billing", "invoice_list.html", "CompanyUnitID", "Belongs to Company");
        $this->langVarList[] = new LangVar("de", "template", "billing", "invoice_list.html", "CompanyUnitID", "Unternehmen/Abteilung");
        $this->langVarList[] = new LangVar("tr", "template", "billing", "invoice_list.html", "CompanyUnitID", "Unternehmen/Abteilung");
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
